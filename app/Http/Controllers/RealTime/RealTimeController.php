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
    private $minute;

    public function __construct()
    {
        $this->middleware([ 'auth', 'organisation:have' ]);
        $this->middleware('can:organisation-status')->only([
            'query'
        ]);

        // gerçek zamanlı tarih aralığı
        $this->minute = Carbon::now()->subMinutes(5)->format('Y-m-d H:i');
    }

    # 
    # gerçek zamanlı akış ekranı.
    # 
    public function stream()
    {
        $pin_groups = PinGroup::where('organisation_id', auth()->user()->organisation_id)->get();

        return view('realTime.stream', compact('pin_groups'));
    }

    # 
    # gerçek zamanlı sorgu.
    # 
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
                        $keywords[] = '('.$k.')';
                        $words[] = $k;
                    }
                }

                if (count($keywords))
                {
                    # 
                    # Twitter Modülü
                    # 
                    if ($group->module_twitter)
                    {
                        $query = @Document::list(
                            [ 'twitter', 'tweets', date('Y.m') ], 'tweet',
                            [
                                'size' => 1000,
                                'query' => [
                                    'bool' => [
                                        'must' => [ [ 'query_string' => [ 'default_field' => 'text', 'query' => implode(' OR ', $keywords) ] ] ],
                                        'filter' => [ 'range' => [ 'created_at' => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ]
                                    ]
                                ],
                                'sort' => [ 'created_at' => 'DESC' ],
                                '_source' => [ 'user.name', 'user.screen_name', 'text', 'created_at' ]
                            ]
                        )->data['hits']['hits'];

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

                    # 
                    # Haber Modülü
                    # 
                    if ($group->module_news)
                    {
                        $query = @Document::list(
                            [ 'articles', '*' ], 'article',
                            [
                                'size' => 100,
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'query_string' => [ 'default_field' => 'description', 'query' => implode(' OR ', $keywords) ] // title
                                            ]
                                        ],
                                        'filter' => [
                                            [ 'range' => [ 'created_at' => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ],
                                            [ 'match' => [ 'status' => 'ok' ] ]
                                        ]
                                    ]
                                ],
                                'sort' => [ 'created_at' => 'DESC' ],
                                '_source' => [ 'url', 'title', 'description', 'created_at' ]
                            ]
                        )->data['hits']['hits'];

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

                    # 
                    # Sözlük Modülü
                    # 
                    if ($group->module_sozluk)
                    {
                        $query = @Document::list(
                            [ 'sozluk', '*' ], 'entry',
                            [
                                'size' => 100,
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'query_string' => [ 'default_field' => 'entry', 'query' => implode(' OR ', $keywords) ] // title
                                            ]
                                        ],
                                        'filter' => [
                                            [ 'range' => [ 'created_at' => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ]
                                        ]
                                    ]
                                ],
                                'sort' => [ 'created_at' => 'DESC' ],
                                '_source' => [ 'url', 'title', 'entry', 'author', 'created_at' ]
                            ]
                        )->data['hits']['hits'];

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

                    # 
                    # Alışveriş Modülü
                    # 
                    if ($group->module_shopping)
                    {
                        $query = @Document::list(
                            [ 'shopping', '*' ], 'product',
                            [
                                'size' => 100,
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'query_string' => [ 'default_field' => 'title', 'query' => implode(' OR ', $keywords) ] // description
                                            ]
                                        ],
                                        'filter' => [
                                            [ 'range' => [ 'created_at' => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ],
                                            [ 'match' => [ 'status' => 'ok' ] ]
                                        ]
                                    ]
                                ],
                                'sort' => [ 'created_at' => 'DESC' ],
                                '_source' => [ 'url', 'title', 'description', 'created_at' ]
                            ]
                        )->data['hits']['hits'];

                        if ($query)
                        {
                            foreach ($query as $object)
                            {
                                $data[] = [
                                    'uuid' => md5($object['_id'].'.'.$object['_index']),
                                    '_id' => $object['_id'],
                                    '_type' => $object['_type'],
                                    '_index' => $object['_index'],
                                    'module' => 'alisveris',
                                    'url' => $object['_source']['url'],
                                    'title' => $object['_source']['title'],
                                    'text' => $object['_source']['description'],
                                    'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at']))
                                ];
                            }
                        }
                    }

                    # 
                    # YouTube Modülü
                    # 
                    if ($group->module_youtube)
                    {
                        // video
                        $query = @Document::list(
                            [ 'youtube', 'videos' ], 'video',
                            [
                                'size' => 100,
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'query_string' => [ 'default_field' => 'description', 'query' => implode(' OR ', $keywords) ] // title
                                            ]
                                        ],
                                        'filter' => [
                                            [ 'range' => [ 'created_at' => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ]
                                        ]
                                    ]
                                ],
                                'sort' => [ 'created_at' => 'DESC' ],
                                '_source' => [ 'title', 'description', 'created_at', 'channel.title', 'channel.id' ]
                            ]
                        )->data['hits']['hits'];

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
                                    'text' => $object['_source']['description'],
                                    'channel' => [
                                        'title' => $object['_source']['channel']['title']
                                    ],
                                    'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at']))
                                ];
                            }
                        }

                        // yorum
                        $query = @Document::list(
                            [ 'youtube', 'comments' ], 'comment',
                            [
                                'size' => 200,
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'query_string' => [ 'default_field' => 'text', 'query' => implode(' OR ', $keywords) ]
                                            ]
                                        ],
                                        'filter' => [
                                            [ 'range' => [ 'created_at' => [ 'format' => 'YYYY-MM-dd HH:mm', 'gte' => $this->minute ] ] ]
                                        ]
                                    ]
                                ],
                                'sort' => [ 'created_at' => 'DESC' ],
                                '_source' => [ 'text', 'channel.title', 'created_at' ]
                            ]
                        )->data['hits']['hits'];

                        if ($query)
                        {
                            foreach ($query as $object)
                            {
                                $data[] = [
                                    'uuid' => md5($object['_id'].'.'.$object['_index']),
                                    '_id' => $object['_id'],
                                    '_type' => $object['_type'],
                                    '_index' => $object['_index'],
                                    'module' => 'youtube-yorum',
                                    'channel' => [
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
        }

        return [
            'status' => 'ok',
            'data' => array_reverse($data),
            'words' => $words
        ];
    }
}
