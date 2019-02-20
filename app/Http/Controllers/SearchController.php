<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Search\ArchiveRequest;
use App\Http\Requests\QRequest;

use App\Elasticsearch\Document;

use Term;

use Carbon\Carbon;

use App\Models\Pin\Group as PinGroup;

class SearchController extends Controller
{
    /**
     * Temel sorgu.
     *
     * @var array
     */
    private $query;

    public function __construct()
    {
        ### [ üyelik ve organizasyon zorunlu ve organizasyonun zorunlu olarak real_time özelliği desteklemesi ] ###
        $this->middleware([ 'auth', 'organisation:have' ]);

        ### [ zorunlu aktif organizasyon ] ###
        $this->middleware('can:organisation-status')->only([
            'search'
        ]);
    }

    /**
     * Arama Ana Sayfa
     *
     * @return view
     */
    public static function dashboard(QRequest $request)
    {
        $q = $request->q;

        return view('search.dashboard', compact('q'));
    }

    /**
     * Arama Sonuçları
     *
     * @return array
     */
    public static function search(ArchiveRequest $request)
    {
        $mquery = [
            'from' => $request->skip,
            'size' => $request->take,
            'sort' => [
                'created_at' => 'DESC'
            ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd',
                                    'gte' => $request->start_date,
                                    'lte' => $request->end_date
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $data = [];

        if ($request->string)
        {
            $clean = Term::cleanSearchQuery($request->string);

            if (is_array($request->modules))
            {
                $modules = array_flip($request->modules);
            }
            else
            {
                $modules = [ $request->modules => 'on' ];
            }

            ### [ twitter modülü ] ###
            if (isset($modules['twitter']))
            {
                $q = $mquery;

                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'default_field' => 'text',
                        'query' => $clean->line,
                        'default_operator' => 'AND'
                    ]
                ];
                $q['query']['bool']['must_not'][] = [ 'match' => [ 'external.type' => 'retweet' ] ];
                $q['_source'] = [ 'user.name', 'user.screen_name', 'text', 'created_at', 'sentiment' ];

                if ($request->sentiment != 'all')
                {
                    $q['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.4 ] ] ];
                }

                $query = @Document::list([ 'twitter', 'tweets', date('Y.m') ], 'tweet', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            'uuid' => md5($object['_id'].'.'.$object['_index']),
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],
                            'sentiment' => $object['_source']['sentiment'],
                            'module' => 'twitter',
                            'user' => [
                                'name' => $object['_source']['user']['name'],
                                'screen_name' => $object['_source']['user']['screen_name']
                            ],
                            'text' => $object['_source']['text'],
                            'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at']))
                        ];
                    }
                }
            }

            ### [ haber modülü ] ###
            if (isset($modules['news']))
            {
                $q = $mquery;

                $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];
                $q['_source'] = [ 'url', 'title', 'description', 'created_at', 'sentiment' ];
                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'fields' => [
                            'description',
                            'title'
                        ],
                        'query' => $clean->line,
                        'default_operator' => 'AND'
                    ]
                ];

                if ($request->sentiment != 'all')
                {
                    $q['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.4 ] ] ];
                }

                $query = @Document::list([ 'media', '*' ], 'article', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            'uuid' => md5($object['_id'].'.'.$object['_index']),
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],
                            'module' => 'haber',
                            'sentiment' => $object['_source']['sentiment'],
                            'url' => $object['_source']['url'],
                            'title' => $object['_source']['title'],
                            'text' => $object['_source']['description'],
                            'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at']))
                        ];
                    }
                }
            }

            ### [ sözlük modülü ] ###
            if (isset($modules['sozluk']))
            {
                $q = $mquery;

                $q['_source'] = [ 'url', 'title', 'entry', 'author', 'created_at', 'sentiment' ];
                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'fields' => [
                            'description',
                            'title'
                        ],
                        'query' => $clean->line,
                        'default_operator' => 'AND'
                    ]
                ];

                if ($request->sentiment != 'all')
                {
                    $q['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.4 ] ] ];
                }

                $query = @Document::list([ 'sozluk', '*' ], 'entry', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            'uuid' => md5($object['_id'].'.'.$object['_index']),
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],
                            'module' => 'sozluk',
                            'sentiment' => $object['_source']['sentiment'],
                            'url' => $object['_source']['url'],
                            'title' => $object['_source']['title'],
                            'text' => $object['_source']['entry'],
                            'author' => $object['_source']['author'],
                            'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at']))
                        ];
                    }
                }
            }

            ### [ alışveriş modülü ] ###
            if (isset($modules['shopping']))
            {
                $q = $mquery;

                $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];
                $q['_source'] = [ 'url', 'title', 'description', 'created_at', 'sentiment' ];
                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'fields' => [
                            'description',
                            'title'
                        ],
                        'query' => $clean->line,
                        'default_operator' => 'AND'
                    ]
                ];

                if ($request->sentiment != 'all')
                {
                    $q['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.4 ] ] ];
                }

                $query = @Document::list([ 'shopping', '*' ], 'product', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $arr = [
                            'uuid' => md5($object['_id'].'.'.$object['_index']),
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],
                            'module' => 'alisveris',
                            'sentiment' => $object['_source']['sentiment'],
                            'url' => $object['_source']['url'],
                            'title' => $object['_source']['title'],
                            'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at']))
                        ];

                        if (@$object['_source']['description'])
                        {
                            $arr['text'] = $object['_source']['description'];
                        }

                        $data[] = $arr;
                    }
                }
            }

            ### [ youtube, video modülü ] ###
            if (isset($modules['youtube_video']))
            {
                $q = $mquery;

                $q['_source'] = [ 'title', 'description', 'created_at', 'channel.title', 'channel.id', 'sentiment' ];
                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'fields' => [
                            'description',
                            'title'
                        ],
                        'query' => $clean->line,
                        'default_operator' => 'AND'
                    ]
                ];

                if ($request->sentiment != 'all')
                {
                    $q['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.4 ] ] ];
                }

                $query = @Document::list([ 'youtube', 'videos' ], 'video', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            'uuid' => md5($object['_id'].'.'.$object['_index']),
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],
                            'module' => 'youtube-video',
                            'sentiment' => $object['_source']['sentiment'],
                            'title' => $object['_source']['title'],
                            'text' => @$object['_source']['description'],
                            'channel' => [
                                'id' => $object['_source']['channel']['id'],
                                'title' => $object['_source']['channel']['title']
                            ],
                            'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at']))
                        ];
                    }
                }
            }

            ### [ youtube, yorum modülü ] ###
            if (isset($modules['youtube_comment']))
            {
                $q = $mquery;

                $q['_source'] = [ 'video_id', 'text', 'channel.id', 'channel.title', 'created_at', 'sentiment' ];
                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'default_field' => 'text',
                        'query' => $clean->line,
                        'default_operator' => 'AND'
                    ]
                ];

                if ($request->sentiment != 'all')
                {
                    $q['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.4 ] ] ];
                }

                $query = @Document::list([ 'youtube', 'comments', '*' ], 'comment', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            'uuid' => md5($object['_id'].'.'.$object['_index']),
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],
                            'module' => 'youtube-comment',
                            'sentiment' => $object['_source']['sentiment'],
                            'video_id' => $object['_source']['video_id'],
                            'channel' => [
                                'id' => $object['_source']['channel']['id'],
                                'title' => $object['_source']['channel']['title']
                            ],
                            'text' => $object['_source']['text'],
                            'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at']))
                        ];
                    }
                }
            }

            return [
                'status' => 'ok',
                'hits' => $data,
                'words' => $clean->words
            ];
        }
        else
        {
            return [
                'status' => 'ok',
                'hits' => [],
                'words' => []
            ];
        }
    }
}
