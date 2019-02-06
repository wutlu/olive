<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Search\ArchiveRequest;
use App\Elasticsearch\Document;

class SearchController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth');
    }

    /**
     * Arama Ana Sayfa
     *
     * @return view
     */
    public static function dashboard()
    {
        return view('search.dashboard');
    }

    /**
     * Arama Sonuçları
     *
     * @return array
     */
    public static function search(ArchiveRequest $request)
    {
        $data = [];
        $words = [];

        $words_raw = str_replace([ ' OR ', ' AND ', ')', '(' ], ' ', $request->string);
        $words_raw = explode(' ', $words_raw);

        foreach ($words_raw as $w)
        {
            if ($w)
            {
                $words[] = $w;
            }
        }

        $modules = array_flip($request->modules);

        $range = [
            'created_at' => [
                'format' => 'YYYY-MM-dd',
                'gte' => $request->start_date,
                'lte' => $request->end_date
            ]
        ];

        ### [ twitter modülü ] ###
        if (isset($modules['twitter']))
        {
            $q = [
                'from' => $request->skip,
                'size' => $request->take,
                'query' => [
                    'bool' => [
                        'must' => [ [ 'query_string' => [ 'default_field' => 'text', 'query' => $request->string ] ] ],
                        'filter' => [
                            [ 'range' => $range ]
                        ]
                    ]
                ],
                'sort' => [ 'created_at' => 'DESC' ],
                '_source' => [ 'user.name', 'user.screen_name', 'text', 'created_at' ]
            ];

            if ($request->sentiment != 'all')
            {
                $q['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.4 ] ] ];
            }

            $query = @Document::list([ 'twitter', 'tweets', '*' ], 'tweet', $q)->data['hits']['hits'];

            if ($query)
            {
                foreach ($query as $object)
                {
                    $data[] = [
                        'uuid' => md5($object['_id'].'.'.$object['_index']),
                        '_id' => $object['_id'],
                        '_type' => $object['_type'],
                        '_index' => $object['_index'],
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
            $q = [
                'from' => $request->skip,
                'size' => $request->take,
                'query' => [
                    'bool' => [
                        'filter' => [
                            [ 'range' => $range ]
                        ],
                        'must' => [
                            [ 'match' => [ 'status' => 'ok' ] ],
                            [ 'query_string' => [ 'fields' => [ 'description', 'title' ], 'query' => $request->string ] ]
                        ]
                    ]
                ],
                'sort' => [ 'created_at' => 'DESC' ],
                '_source' => [ 'url', 'title', 'description', 'created_at' ]
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
            $q = [
                'from' => $request->skip,
                'size' => $request->take,
                'query' => [
                    'bool' => [
                        'filter' => [
                            [ 'range' => $range ]
                        ],
                        'must' => [
                            [ 'query_string' => [ 'fields' => [ 'description', 'title' ], 'query' => $request->string ] ]
                        ]
                    ]
                ],
                'sort' => [ 'created_at' => 'DESC' ],
                '_source' => [ 'url', 'title', 'entry', 'author', 'created_at' ]
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
            $q = [
                'from' => $request->skip,
                'size' => $request->take,
                'query' => [
                    'bool' => [
                        'filter' => [
                            [ 'range' => $range ],
                            [ 'match' => [ 'status' => 'ok' ] ]
                        ],
                        'must' => [
                            [ 'query_string' => [ 'fields' => [ 'description', 'title' ], 'query' => $request->string ] ]
                        ]
                    ]
                ],
                'sort' => [ 'created_at' => 'DESC' ],
                '_source' => [ 'url', 'title', 'description', 'created_at' ]
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
            $q = [
                'from' => $request->skip,
                'size' => $request->take,
                'query' => [
                    'bool' => [
                        'filter' => [
                            [ 'range' => $range ]
                        ],
                        'must' => [
                            [ 'query_string' => [ 'fields' => [ 'description', 'title' ], 'query' => $request->string ] ]
                        ]
                    ]
                ],
                'sort' => [ 'created_at' => 'DESC' ],
                '_source' => [ 'title', 'description', 'created_at', 'channel.title', 'channel.id' ]
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
                        'title' => $object['_source']['title'],
                        'text' => @$object['_source']['description'],
                        'channel' => [
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
            $q = [
                'from' => $request->skip,
                'size' => $request->take,
                'query' => [
                    'bool' => [
                        'filter' => [
                            [ 'range' => $range ]
                        ],
                        'must' => [
                            [ 'query_string' => [ 'default_field' => 'text', 'query' => $request->string ] ]
                        ]
                    ]
                ],
                'sort' => [ 'created_at' => 'DESC' ],
                '_source' => [
                    'video_id',
                    'text',
                    'channel.id',
                    'channel.title',
                    'created_at'
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
            'hits' => array_reverse($data),
            'words' => $words
        ];
    }
}
