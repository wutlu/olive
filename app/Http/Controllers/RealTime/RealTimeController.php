<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\Pin\Group as PinGroup;
use App\Models\RealTime\KeywordGroup;

use App\Http\Requests\RealTime\RealTimeRequest;

use App\Elasticsearch\Document;
use App\Utilities\Term;

use Carbon\Carbon;

class RealTimeController extends Controller
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
            'query'
        ]);

        ### [ temel sorgu ] ###
        $this->query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'called_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => Carbon::now()->subMinutes(2)->format('Y-m-d H:i')
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'sort' => [ 'created_at' => 'DESC' ],
        ];
    }

    /**
     * Gerçek Zamanlı, akış ekranı.
     *
     * @return view
     */
    public function stream()
    {
        $pin_groups = PinGroup::where('organisation_id', auth()->user()->organisation_id)->orderBy('updated_at', 'DESC')->limit(4)->get();

        return view('realTime.stream', compact('pin_groups'));
    }

    /**
     * Gerçek Zamanlı, akış sorgusu.
     *
     * @return array
     */
    public function query(RealTimeRequest $request)
    {
        $user = auth()->user();

        $data = [];
        $words = [];

        $groups = KeywordGroup::whereIn('id', $request->keyword_group)->where('organisation_id', $user->organisation_id)->get();

        if (count($groups))
        {
            foreach ($groups as $group)
            {
                $keywords = [];

                if ($group->keywords)
                {
                    foreach (explode(PHP_EOL, $group->keywords) as $k)
                    {
                        $clean = Term::cleanSearchQuery($k);

                        foreach ($clean->words as $w)
                        {
                            if ($w)
                            {
                                $words[] = $w;
                            }
                        }

                        $keywords[] = '('.$clean->line.')';
                    }
                }

                if (count($keywords))
                {
                    ### [ twitter modülü ] ###
                    if ($group->module_twitter)
                    {
                        $q = $this->query;

                        $q['size'] = 1000;
                        $q['query']['bool']['must'][] = [
                            'query_string' => [
                                'default_field' => 'text',
                                'query' => implode(' OR ', $keywords),
                                'default_operator' => 'AND'
                            ]
                        ];
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
                }

                ### [ haber modülü ] ###
                if ($group->module_news)
                {
                    $q = $this->query;

                    $q['size'] = 100;
                    $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];
                    $q['_source'] = [ 'url', 'title', 'description', 'created_at', 'sentiment' ];

                    if (count($keywords))
                    {
                        $q['query']['bool']['must'][] = [
                            'query_string' => [
                                'fields' => [
                                    'description',
                                    'title'
                                ],
                                'query' => implode(' OR ', $keywords),
                                'default_operator' => 'AND'
                            ]
                        ];
                    }

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
                if ($group->module_sozluk)
                {
                    $q = $this->query;

                    $q['size'] = 100;
                    $q['_source'] = [ 'url', 'title', 'entry', 'author', 'created_at', 'sentiment' ];

                    if (count($keywords))
                    {
                        $q['query']['bool']['must'][] = [
                            'query_string' => [
                                'fields' => [
                                    'description',
                                    'title'
                                ],
                                'query' => implode(' OR ', $keywords),
                                'default_operator' => 'AND'
                            ]
                        ];
                    }

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
                if ($group->module_shopping)
                {
                    $q = $this->query;

                    $q['size'] = 100;
                    $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];
                    $q['_source'] = [ 'url', 'title', 'description', 'created_at', 'sentiment' ];

                    if (count($keywords))
                    {
                        $q['query']['bool']['must'][] = [
                            'query_string' => [
                                'fields' => [
                                    'description',
                                    'title'
                                ],
                                'query' => implode(' OR ', $keywords),
                                'default_operator' => 'AND'
                            ]
                        ];
                    }

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
                if ($group->module_youtube_video)
                {
                    $q = $this->query;

                    $q['size'] = 100;
                    $q['_source'] = [ 'title', 'description', 'created_at', 'channel.title', 'channel.id', 'sentiment' ];

                    if (count($keywords))
                    {
                        $q['query']['bool']['must'][] = [
                            'query_string' => [
                                'fields' => [
                                    'description',
                                    'title'
                                ],
                                'query' => implode(' OR ', $keywords),
                                'default_operator' => 'AND'
                            ]
                        ];
                    }

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
                                    'title' => $object['_source']['channel']['title']
                                ],
                                'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at']))
                            ];
                        }
                    }
                }

                ### [ youtube, yorum modülü ] ###
                if ($group->module_youtube_comment)
                {
                    $q = $this->query;

                    $q['size'] = 200;
                    $q['_source'] = [ 'video_id', 'text', 'channel.id', 'channel.title', 'created_at', 'sentiment' ];

                    if (count($keywords))
                    {
                        $q['query']['bool']['must'][] = [
                            'query_string' => [
                                'default_field' => 'text',
                                'query' => implode(' OR ', $keywords),
                                'default_operator' => 'AND'
                            ]
                        ];
                    }

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
            }
        }

        usort($data, '\App\Utilities\DateUtility::dateSort');

        return [
            'status' => 'ok',
            'data' => $data,
            'words' => $words
        ];
    }
}
