<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Search\ArchiveRequest;
use App\Http\Requests\Search\ArchiveAggregationRequest;
use App\Http\Requests\QRequest;

use App\Elasticsearch\Document;

use Term;

use Carbon\Carbon;

use App\Models\Pin\Group as PinGroup;
use App\Models\Crawlers\MediaCrawler;

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
            'search',
            'aggregation'
        ]);

        ### [ 500 işlemden sonra 60 dakika ile sınırla ] ###
        $this->middleware('throttle:500,60')->only([
            'search',
            'aggregation'
        ]);
    }

    /**
     * Arama Sonuçları için Aggregation
     *
     * @return view
     */
    public static function aggregation(ArchiveAggregationRequest $request)
    {
        $mquery = [
            'size' => 0,
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'dd.MM.YYYY',
                                    'gte' => $request->start_date,
                                    'lte' => $request->end_date
                                ]
                            ]
                        ]
                    ],
                    'must' => [
                        [ 'exists' => [ 'field' => 'created_at' ] ],
                        [
                            'query_string' => [
                                'query' => $request->string,
                                'default_operator' => 'AND'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if ($request->sentiment != 'all')
        {
            $q['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.4 ] ] ];
        }

        switch ($request->type)
        {
            case 'hourly':
                $arr = array_merge($mquery, [
                    'aggs' => [
                        'results' => [
                            'histogram' => [
                                'script' => 'doc.created_at.value.getHourOfDay()',
                                'interval' => 1,
                                'min_doc_count' => 0,
                                'extended_bounds' => [ 'min' => 1, 'max' => 23 ]
                            ]
                        ]
                    ]
                ]);

                $query = Document::list([ '*' ], 'tweet,article,entry,video,comment', $arr);

                $data = [
                    'results' => $query->data['aggregations']['results']['buckets'],
                    'hits' => $query->data['hits']['total']
                ];
            break;

            case 'daily':
                $arr = array_merge($mquery, [
                    'aggs' => [
                        'results' => [
                            'histogram' => [
                                'script' => 'doc.created_at.value.getDayOfWeek()',
                                'interval' => 1,
                                'min_doc_count' => 0,
                                'extended_bounds' => [ 'min' => 1, 'max' => 6 ]
                            ]
                        ]
                    ]
                ]);

                $query = Document::list([ '*' ], 'tweet,article,entry,video,comment', $arr);

                $data = [
                    'results' => $query->data['aggregations']['results']['buckets'],
                    'hits' => $query->data['hits']['total']
                ];
            break;

            case 'location':
                $arr = array_merge($mquery, [
                    'aggs' => [
                        'places' => [
                            'terms' => [
                                'field' => 'place.full_name'
                            ]
                        ]
                    ]
                ]);

                $query = Document::list([ 'twitter', 'tweets', '*' ], 'tweet', $arr);

                $data = [
                    'results' => $query->data['aggregations']['places']['buckets'],
                    'hits' => $query->data['hits']['total']
                ];
            break;

            case 'platform':
                $arr = array_merge($mquery, [
                    'aggs' => [
                        'platforms' => [
                            'terms' => [
                                'field' => 'platform'
                            ]
                        ]
                    ]
                ]);

                $query = Document::list([ 'twitter', 'tweets', '*' ], 'tweet', $arr);

                $data = [
                    'results' => $query->data['aggregations']['platforms']['buckets'],
                    'hits' => $query->data['hits']['total']
                ];
            break;

            case 'mention':
                $arr = array_merge($mquery, [
                    'aggs' => [
                        'twitter' => [
                            'terms' => [
                                'field' => 'user.screen_name',
                                'size' => 10
                            ]
                        ],
                        'sozluk' => [
                            'terms' => [
                                'field' => 'author',
                                'size' => 10
                            ]
                        ],
                        'youtube_video' => [
                            'terms' => [
                                'field' => 'channel.title',
                                'size' => 10
                            ]
                        ],
                        'youtube_comment' => [
                            'terms' => [
                                'field' => 'channel.title',
                                'size' => 10
                            ]
                        ],
                        'article' => [
                            'terms' => [
                                'field' => 'site_id',
                                'size' => 10
                            ]
                        ]
                    ]
                ]);

                $query = Document::list([ '*' ], 'tweet,entry,video,comment,article', $arr);

                $modules = [];

                foreach ($query->data['aggregations'] as $key => $module)
                {
                    if ($key == 'article')
                    {
                        $modules[$key] = array_map(function($item) {
                            $site = MediaCrawler::where('id', $item['key'])->first();

                            $item['key'] = @$site ? $site->site : 'N/A('.$item['key'].')';

                            return $item;
                        }, $module['buckets']);
                    }
                    else
                    {
                        $modules[$key] = $module['buckets'];
                    }
                }

                $data = [
                    'results' => $modules,
                    'hits' => $query->data['hits']['total']
                ];
            break;

            case 'hashtag':
                $arr = array_merge($mquery, [
                    'aggs' => [
                        'hashtag' => [
                            'nested' => [ 'path' => 'entities.hashtags' ],
                            'aggs' => [
                                'hit_items' => [
                                    'terms' => [
                                        'field' => 'entities.hashtags.hashtag',
                                        'size' => 15
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);

                $query = Document::list([ '*' ], 'tweet,entry,video,comment,article', $arr);

                $data = [
                    'results' => $query->data['aggregations']['hashtag']['hit_items']['buckets'],
                    'hits' => $query->data['hits']['total']
                ];
            break;

            case 'source':
            # ------------------------------------------------------ #

            $arr = $mquery;

            unset($arr['size']);

            $data = [
                'tweet' => Document::count([ 'twitter', 'tweets', '*' ], 'tweet', $arr),
                'article' => Document::count([ 'media', 's*' ], 'article', $arr),
                'comment' => Document::count([ 'youtube', 'comments', '*' ], 'comment', $arr),
                'video' => Document::count([ 'youtube', 'videos' ], 'video', $arr),
                'entry' => Document::count([ 'sozluk', '*' ], 'entry', $arr),
            ];

            # ------------------------------------------------------ #
            break;
        }

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    /**
     * Arama Ana Sayfa
     *
     * @return view
     */
    public static function dashboard(QRequest $request)
    {
        $q = $request->q;
        $s = $request->s;
        $e = $request->e;

        return view('search', compact('q', 's', 'e'));
    }

    /**
     * Arama Sonuçları
     *
     * @return array
     */
    public static function search(ArchiveRequest $request)
    {
        $data = [];

        $clean = Term::cleanSearchQuery($request->string);

        $q = [
            'from' => $request->skip,
            'size' => $request->take,
            'sort' => [ 'created_at' => 'DESC' ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'dd.MM.YYYY',
                                    'gte' => $request->start_date,
                                    'lte' => $request->end_date
                                ]
                            ]
                        ]
                    ],
                    'must' => [
                        [
                            'query_string' => [
                                'query' => $clean->line,
                                'default_operator' => 'AND'
                            ]
                        ],
                        [ 'exists' => [ 'field' => 'created_at' ] ],
                    ],
                    'should' => [
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ],
                    'must_not' => [
                        [ 'match' => [ 'external.type' => 'retweet' ] ]
                    ]
                ]
            ],
            '_source' => [
                'user.name',
                'user.screen_name',
                'text',
                'created_at',

                'url',
                'title',
                'description',

                'entry',
                'author',

                'channel.title',
                'channel.id',

                'video_id'
            ]
        ];

        if ($request->sentiment != 'all')
        {
            $q['query']['bool']['filter'][] = [
                'range' => [
                    implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.4 ]
                ]
            ];
        }

        $modules = [];

        foreach ($request->modules as $module)
        {
            switch ($module)
            {
                case 'twitter'        : $modules[] = 'tweet';   break;
                case 'sozluk'         : $modules[] = 'entry';   break;
                case 'news'           : $modules[] = 'article'; break;
                case 'youtube_video'  : $modules[] = 'video';   break;
                case 'youtube_comment': $modules[] = 'comment'; break;
                case 'shopping'       : $modules[] = 'product'; break;
            }
        }

        $query = Document::list([ '*' ], implode(',', $modules), $q);

        $stats = [
            'took' => 0,
            'hits' => 0,
        ];

        if (@$query->data['hits']['hits'])
        {
            $stats['took'] = $query->data['took']/1000;
            $stats['hits'] = number_format($query->data['hits']['total']);

            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = [
                    'uuid' => md5($object['_id'].'.'.$object['_index']),
                    '_id' => $object['_id'],
                    '_type' => $object['_type'],
                    '_index' => $object['_index'],

                    'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at'])),
                ];

                switch ($object['_type'])
                {
                    case 'tweet':
                        $data[] = array_merge($arr, [
                            'user' => [
                                'name' => $object['_source']['user']['name'],
                                'screen_name' => $object['_source']['user']['screen_name']
                            ],
                            'text' => $object['_source']['text'],
                        ]);
                    break;
                    case 'article':
                        $data[] = array_merge($arr, [
                            'url' => $object['_source']['url'],
                            'title' => $object['_source']['title'],
                            'text' => $object['_source']['description'],
                        ]);
                    break;
                    case 'entry':
                        $data[] = array_merge($arr, [
                            'url' => $object['_source']['url'],
                            'title' => $object['_source']['title'],
                            'text' => $object['_source']['entry'],
                            'author' => $object['_source']['author'],
                        ]);
                    break;
                    case 'product':
                        if (@$object['_source']['description'])
                        {
                            $arr['text'] = $object['_source']['description'];
                        }

                        $data[] = array_merge($arr, [
                            'url' => $object['_source']['url'],
                            'title' => $object['_source']['title'],
                        ]);
                    break;
                    case 'video':
                        $data[] = array_merge($arr, [
                            'title' => $object['_source']['title'],
                            'text' => @$object['_source']['description'],
                            'channel' => [
                                'id' => $object['_source']['channel']['id'],
                                'title' => $object['_source']['channel']['title']
                            ],
                        ]);
                    break;
                    case 'comment':
                        $data[] = array_merge($arr, [
                            'video_id' => $object['_source']['video_id'],
                            'channel' => [
                                'id' => $object['_source']['channel']['id'],
                                'title' => $object['_source']['channel']['title']
                            ],
                            'text' => $object['_source']['text'],
                        ]);
                    break;
                }
            }
        }

        return [
            'status' => 'ok',
            'hits' => $data,
            'words' => $clean->words,
            'stats' => $stats
        ];
    }
}
