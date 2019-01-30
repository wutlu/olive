<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\Pin\Group as PinGroup;
use App\Models\RealTime\KeywordGroup;

use App\Http\Requests\RealTime\RealTimeRequest;

use App\Elasticsearch\Document;

use Carbon\Carbon;

class RealTimeController extends Controller
{
    /**
     * Gerçek Zamanlı, son bir kaç dakika değeri.
     *
     * @var datetime
     */
    private $minute;

    /**
     * Gerçek Zamanlı, sorgu yapılacak kolon.
     *
     * @var string
     */
    private $range;

    public function __construct()
    {
        ### [ üyelik ve organizasyon zorunlu ve organizasyonun zorunlu olarak real_time özelliği desteklemesi ] ###
        $this->middleware([ 'auth', 'organisation:have,real_time' ]);

        ### [ zorunlu aktif organizasyon ] ###
        $this->middleware('can:organisation-status')->only([
            'query'
        ]);

        ### [ gerçek zamanlı son bir kaç dakika değeri ] ###
        $this->minute = Carbon::now()->subMinutes(2)->format('Y-m-d H:i');
        ### [ gerçek zamanlı sorgu yapılacak kolon ] ###
        $this->range_column = 'created_at';
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
                        $keywords[] = '('.strtolower($k).')';

                        $words_raw = str_replace([ ' OR ', ' AND ', ')', '(' ], ' ', $k);
                        $words_raw = explode(' ', $words_raw);

                        foreach ($words_raw as $w)
                        {
                            if ($w)
                            {
                                $words[] = $w;
                            }
                        }
                    }
                }

                if (count($keywords))
                {
                    ### [ twitter modülü ] ###
                    if ($group->module_twitter)
                    {
                        $q = [
                            'size' => 1000,
                            'query' => [
                                'bool' => [
                                    'must' => [ [ 'query_string' => [ 'default_field' => 'text', 'query' => implode(' OR ', $keywords) ] ] ],
                                    'filter' => [ 'range' => [ $this->range_column => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ]
                                ]
                            ],
                            'sort' => [ 'created_at' => 'DESC' ],
                            '_source' => [ 'user.name', 'user.screen_name', 'text', 'created_at' ]
                        ];

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
                    $q = [
                        'size' => 100,
                        'query' => [
                            'bool' => [
                                'filter' => [
                                    [ 'range' => [ $this->range_column => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ],
                                    [ 'match' => [ 'status' => 'ok' ] ]
                                ]
                            ]
                        ],
                        'sort' => [ 'created_at' => 'DESC' ],
                        '_source' => [ 'url', 'title', 'description', 'created_at' ]
                    ];

                    if (count($keywords))
                    {
                        $q['query']['bool']['must'][] = [
                            'query_string' => [ 'fields' => [ 'description', 'title' ], 'query' => implode(' OR ', $keywords) ]
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
                    $q = [
                        'size' => 100,
                        'query' => [
                            'bool' => [
                                'filter' => [
                                    [ 'range' => [ $this->range_column => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ]
                                ]
                            ]
                        ],
                        'sort' => [ 'created_at' => 'DESC' ],
                        '_source' => [ 'url', 'title', 'entry', 'author', 'created_at' ]
                    ];

                    if (count($keywords))
                    {
                        $q['query']['bool']['must'][] = [
                            'query_string' => [ 'fields' => [ 'description', 'title' ], 'query' => implode(' OR ', $keywords) ]
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
                    $q = [
                        'size' => 100,
                        'query' => [
                            'bool' => [
                                'filter' => [
                                    [ 'range' => [ $this->range_column => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ],
                                    [ 'match' => [ 'status' => 'ok' ] ]
                                ]
                            ]
                        ],
                        'sort' => [ 'created_at' => 'DESC' ],
                        '_source' => [ 'url', 'title', 'description', 'created_at' ]
                    ];

                    if (count($keywords))
                    {
                        $q['query']['bool']['must'][] = [
                            'query_string' => [ 'fields' => [ 'description', 'title' ], 'query' => implode(' OR ', $keywords) ]
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
                    $q = [
                        'size' => 100,
                        'query' => [
                            'bool' => [
                                'filter' => [
                                    [ 'range' => [ $this->range_column => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ]
                                ]
                            ]
                        ],
                        'sort' => [ 'created_at' => 'DESC' ],
                        '_source' => [ 'title', 'description', 'created_at', 'channel.title', 'channel.id' ]
                    ];

                    if (count($keywords))
                    {
                        $q['query']['bool']['must'][] = [
                            'query_string' => [ 'fields' => [ 'description', 'title' ], 'query' => implode(' OR ', $keywords) ]
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
                    $q = [
                        'size' => 200,
                        'query' => [
                            'bool' => [
                                'filter' => [ 'range' => [ $this->range_column => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ]
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

                    if (count($keywords))
                    {
                        $q['query']['bool']['must'][] = [
                            'query_string' => [ 'default_field' => 'text', 'query' => implode(' OR ', $keywords) ]
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

        return [
            'status' => 'ok',
            'data' => array_reverse($data),
            'words' => $words
        ];
    }
}
