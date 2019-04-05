<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\Pin\Group as PinGroup;
use App\Models\RealTime\KeywordGroup;

use App\Http\Requests\RealTime\RealTimeRequest;

use App\Elasticsearch\Document;
use App\Utilities\Term;
use App\Utilities\Crawler;

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
    }

    /**
     * Gerçek Zamanlı, akış ekranı.
     *
     * @return view
     */
    public function stream()
    {
        return view('stream');
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

                $selected_modules = $group->modules ? $group->modules : [];

                ### [ twitter modülü ] ###
                if (in_array('twitter', $selected_modules))
                {
                    if (count($keywords))
                    {
                        $q = [
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
                                    ],
                                    'must' => [
                                        'query_string' => [
                                            'default_field' => 'text',
                                            'query' => implode(' OR ', $keywords),
                                            'default_operator' => 'AND'
                                        ]
                                    ]
                                ]
                            ],
                            'sort' => [ 'created_at' => 'DESC' ],
                            'size' => 1000,
                            '_source' => [
                                'user.name',
                                'user.screen_name',
                                'user.image',
                                'text',
                                'created_at',
                                'sentiment',
                                'deleted_at'
                            ]
                        ];

                        if ($request->sentiment != 'all')
                        {
                            $q['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.34 ] ] ];
                        }

                        $query = Document::search([ 'twitter', 'tweets', date('Y.m') ], 'tweet', $q);

                        if (@$query->data['hits']['hits'])
                        {
                            foreach ($query->data['hits']['hits'] as $object)
                            {
                                $arr = [
                                    'uuid' => md5($object['_id'].'.'.$object['_index']),
                                    '_id' => $object['_id'],
                                    '_type' => $object['_type'],
                                    '_index' => $object['_index'],
                                    'sentiment' => Crawler::emptySentiment(@$object['_source']['sentiment']),
                                    'module' => 'twitter',
                                    'user' => [
                                        'name' => $object['_source']['user']['name'],
                                        'screen_name' => $object['_source']['user']['screen_name'],
                                        'image' => $object['_source']['user']['image']
                                    ],
                                    'text' => Term::tweet($object['_source']['text']),
                                    'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at']))
                                ];

                                if (@$object['_source']['deleted_at'])
                                {
                                    $arr['deleted_at'] = $object['_source']['deleted_at'];
                                }

                                $data[] = $arr;
                            }
                        }
                    }
                }

                $haystack = $selected_modules;

                $target = [ 'youtube_video', 'youtube_comment', 'shopping', 'news', 'sozluk' ];

                if (count(array_intersect($haystack, $target)) > 0)
                {
                    $q = [
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
                                ],
                                'should' => [
                                    [ 'match' => [ 'status' => 'ok' ] ]
                                ],
                                'must' => [
                                    [ 'exists' => [ 'field' => 'created_at' ] ]
                                ]
                            ]
                        ],
                        'sort' => [ 'created_at' => 'DESC' ],
                        'size' => 1000,
                        '_source' => [
                            'url',
                            'title',
                            'description',
                            'created_at',
                            'sentiment',

                            'entry',
                            'author',

                            'channel.title',
                            'channel.id',

                            'video_id',
                            'text',

                            'deleted_at'
                        ]
                    ];

                    if ($request->sentiment != 'all')
                    {
                        $q['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.4 ] ] ];
                    }

                    if (count($keywords))
                    {
                        $q['query']['bool']['must'][] = [
                            'query_string' => [
                                'fields' => [
                                    'title',
                                    'description',
                                    'entry',
                                    'text'
                                ],
                                'query' => implode(' OR ', $keywords),
                                'default_operator' => 'AND'
                            ]
                        ];
                    }

                    $modules = [];

                    foreach ($selected_modules as $module)
                    {
                        switch ($module)
                        {
                            case 'sozluk'         : $modules[] = 'entry';   break;
                            case 'news'           : $modules[] = 'article'; break;
                            case 'youtube_video'  : $modules[] = 'video';   break;
                            case 'youtube_comment': $modules[] = 'comment'; break;
                            case 'shopping'       : $modules[] = 'product'; break;
                        }
                    }

                    $query = Document::search([ '*' ], implode(',', $modules), $q);

                    if (@$query->data['hits']['hits'])
                    {
                        foreach ($query->data['hits']['hits'] as $object)
                        {
                            $arr = [
                                'uuid' => md5($object['_id'].'.'.$object['_index']),
                                '_id' => $object['_id'],
                                '_type' => $object['_type'],
                                '_index' => $object['_index'],
                                'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at'])),
                                'sentiment' => Crawler::emptySentiment(@$object['_source']['sentiment'])
                            ];

                            if (@$object['_source']['deleted_at'])
                            {
                                $arr['deleted_at'] = $object['_source']['deleted_at'];
                            }

                            switch ($object['_type'])
                            {
                                case 'article':
                                    $data[] = array_merge($arr, [
                                        'url' => $object['_source']['url'],
                                        'title' => $object['_source']['title'],
                                        'text' => $object['_source']['description']
                                    ]);
                                break;
                                case 'entry':
                                    $data[] = array_merge($arr, [
                                        'url' => $object['_source']['url'],
                                        'title' => $object['_source']['title'],
                                        'text' => $object['_source']['entry'],
                                        'author' => $object['_source']['author']
                                    ]);
                                break;
                                case 'product':
                                    if (@$object['_source']['description'])
                                    {
                                        $arr['text'] = $object['_source']['description'];
                                    }

                                    $data[] = array_merge($arr, [
                                        'url' => $object['_source']['url'],
                                        'title' => $object['_source']['title']
                                    ]);
                                break;
                                case 'video':
                                    $data[] = array_merge($arr, [
                                        'title' => $object['_source']['title'],
                                        'text' => @$object['_source']['description'],
                                        'channel' => [
                                            'title' => $object['_source']['channel']['title']
                                        ]
                                    ]);
                                break;
                                case 'comment':
                                    $data[] = array_merge($arr, [
                                        'video_id' => $object['_source']['video_id'],
                                        'channel' => [
                                            'id' => $object['_source']['channel']['id'],
                                            'title' => $object['_source']['channel']['title']
                                        ],
                                        'text' => $object['_source']['text']
                                    ]);
                                break;
                            }
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
