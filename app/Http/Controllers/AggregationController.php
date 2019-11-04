<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Requests\Search\ArchiveAggregationRequest;
use App\Http\Requests\Search\SaveRequest;
use App\Http\Requests\QRequest;
use App\Http\Requests\IdRequest;

use App\Elasticsearch\Document;

use Term;

use App\Models\SavedSearch;
use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\BlogCrawler;
use App\Models\Crawlers\ShoppingCrawler;

use App\Utilities\Crawler;

class AggregationController extends Controller
{
    /**
     * Temel sorgu.
     *
     * @var array
     */
    private $query;

    public function __construct()
    {
        ### [ üyelik ve organizasyon zorunlu ve organizasyonun zorunlu ] ###
        $this->middleware([ 'auth', 'organisation:have' ]);

        ### [ zorunlu aktif organizasyon ] ###
        $this->middleware([
            'can:organisation-status',
            'organisation:have,module_search'
        ])->only([
            'search',
            'banner'
        ]);

        ### [ 500 işlemden sonra 5 dakika ile sınırla ] ###
        $this->middleware('throttle:500,5')->only([
            'search',
            'banner'
        ]);
    }

    /**
     * Aggregation Sonuçları
     *
     * @return array
     */
    public static function search(ArchiveAggregationRequest $request)
    {
        $organisation = auth()->user()->organisation;

        $data = [
            'organisation' => $organisation,
            'request' => $request
        ];

        $clean = Term::cleanSearchQuery($request->string);

        $q = [
            'size' => 0,
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
                    ],
                    'must' => [
                        [ 'exists' => [ 'field' => 'created_at' ] ],
                        [
                            'query_string' => [
                                'fields' => [
                                    'title',
                                    'description',
                                    'entry',
                                    'text'
                                ],
                                'query' => $clean->line,
                                'default_operator' => 'AND'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if ($request->category)
        {
            $q['query']['bool']['must'][] = [ 'match' => [ 'category' => config('system.analysis.category.types')[$request->category]['title'] ] ];
        }

        foreach ([ [ 'consumer' => [ 'nws', 'que', 'req', 'cmp' ] ], [ 'sentiment' => [ 'pos', 'neg', 'neu', 'hte' ] ] ] as $key => $bucket)
        {
            foreach ($bucket as $key => $b)
            {
                foreach ($b as $o)
                {
                    if ($request->{$key.'_'.$o})
                    {
                        $q['query']['bool']['must'][] = [
                            'range' => [
                                implode('.', [ $key, $o ]) => [
                                    'gte' => implode('.', [ 0, $request->{$key.'_'.$o} ])
                                ]
                            ]
                        ];
                    }
                }
            }
        }

        switch ($request->type)
        {
            case 'histogram':
                $aggs['all'] = [
                    'daily' => [
                        'histogram' => [
                            'script' => 'doc.created_at.value.getDayOfWeek()',
                            'interval' => 1
                        ]
                    ],
                    'hourly' =>[
                        'histogram' => [
                            'script' => 'doc.created_at.value.getHourOfDay()',
                            'interval' => 1
                        ]
                    ]
                ];
            break;
            case 'place':
                $aggs['twitter'] = [
                    'place' => [
                        'terms' => [
                            'field' => 'place.name',
                            'size' => 15
                        ]
                    ]
                ];
                $aggs['instagram'] = [
                    'place' => [
                        'terms' => [
                            'field' => 'place.name',
                            'size' => 15
                        ]
                    ]
                ];
            break;
            case 'category':
                $aggs['all'] = [
                    'category' => [
                        'terms' => [
                            'field' => 'category',
                            'size' => 100
                        ]
                    ]
                ];
            break;
            case 'local_press':
                $aggs['news'] = [
                    'locals' => [
                        'terms' => [
                            'field' => 'state',
                            'size' => 100
                        ]
                    ]
                ];
            break;
            case 'platform':
                $aggs['twitter'] = [
                    'platform' => [
                        'terms' => [
                            'field' => 'platform',
                            'size' => 10
                        ]
                    ]
                ];
            break;
            case 'author':
                $aggs['twitter'] = [
                    'users' => [
                        'terms' => [
                            'field' => 'user.id',
                            'size' => 100
                        ],
                        'aggs' => [
                            'properties' => [
                                'top_hits' => [
                                    'size' => 1,
                                    '_source' => [
                                        'include' => [
                                            'user.name',
                                            'user.screen_name'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],

                    'influencers' => [
                        'terms' => [
                            'field' => 'user.id',
                            'size' => 100
                        ],
                        'aggs' => [
                            'properties' => [
                                'top_hits' => [
                                    'size' => 1,
                                    '_source' => [
                                        'include' => [
                                            'user.name',
                                            'user.screen_name',
                                            'user.counts.followers'
                                        ]
                                    ]
                                ]
                            ],
                            'total_followers' => [
                                'avg' => [
                                    'field' => 'user.counts.followers'
                                ]
                            ],
                            'followers_bucket_sort' => [
                                'bucket_sort' => [
                                    'sort' => [
                                        [ 'total_followers' => [ 'order' => 'desc' ] ]
                                    ]
                                ]
                            ]
                        ]
                    ],

                    'mentions' => [
                        'nested' => [ 'path' => 'entities.mentions' ],
                        'aggs' => [
                            'hits' => [
                                'terms' => [
                                    'field' => 'entities.mentions.mention.id',
                                    'size' => 100
                                ],
                                'aggs' => [
                                    'properties' => [
                                        'top_hits' => [
                                            'size' => 1,
                                            '_source' => [
                                                'include' => [
                                                    'entities.mentions.mention.name',
                                                    'entities.mentions.mention.screen_name'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                $aggs['youtube_video'] = [
                    'users' => [
                        'terms' => [
                            'field' => 'channel.id',
                            'size' => 100
                        ],
                        'aggs' => [
                            'properties' => [
                                'top_hits' => [
                                    'size' => 1,
                                    '_source' => [
                                        'include' => [
                                            'channel.title'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                $aggs['youtube_comment'] = [
                    'users' => [
                        'terms' => [
                            'field' => 'channel.id',
                            'size' => 100
                        ],
                        'aggs' => [
                            'properties' => [
                                'top_hits' => [
                                    'size' => 1,
                                    '_source' => [
                                        'include' => [
                                            'channel.title'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                $aggs['sozluk'] = [
                    'users' => [
                        'terms' => [
                            'field' => 'author',
                            'size' => 100
                        ],
                        'aggs' => [
                            'properties' => [
                                'top_hits' => [
                                    'size' => 1,
                                    '_source' => [
                                        'include' => [
                                            'site_id'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'sites' => [
                        'terms' => [
                            'field' => 'site_id',
                            'size' => 100
                        ]
                    ],
                    'topics' => [
                        'terms' => [
                            'field' => 'group_name',
                            'size' => 100
                        ],
                        'aggs' => [
                            'properties' => [
                                'top_hits' => [
                                    'size' => 1,
                                    '_source' => [
                                        'include' => [
                                            'title',
                                            'site_id'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                $aggs['shopping'] = [
                    'users' => [
                        'terms' => [
                            'field' => 'seller.name',
                            'size' => 100
                        ],
                        'aggs' => [
                            'properties' => [
                                'top_hits' => [
                                    'size' => 1,
                                    '_source' => [
                                        'include' => [
                                            'site_id'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'sites' => [
                        'terms' => [
                            'field' => 'site_id',
                            'size' => 100
                        ]
                    ]
                ];
                $aggs['blog'] = [
                    'sites' => [
                        'terms' => [
                            'field' => 'site_id',
                            'size' => 100
                        ]
                    ]
                ];
                $aggs['news'] = [
                    'sites' => [
                        'terms' => [
                            'field' => 'site_id',
                            'size' => 100
                        ]
                    ]
                ];
            break;
            case 'hashtag':
                $aggs['twitter'] = [
                    'hashtag' => [
                        'nested' => [ 'path' => 'entities.hashtags' ],
                        'aggs' => [
                            'hits' => [
                                'terms' => [
                                    'field' => 'entities.hashtags.hashtag',
                                    'size' => 10
                                ]
                            ]
                        ]
                    ]
                ];
                $aggs['instagram'] = [
                    'hashtag' => [
                        'nested' => [ 'path' => 'entities.hashtags' ],
                        'aggs' => [
                            'hits' => [
                                'terms' => [
                                    'field' => 'entities.hashtags.hashtag',
                                    'size' => 10
                                ]
                            ]
                        ]
                    ]
                ];
                $aggs['youtube_video'] = [
                    'hashtag' => [
                        'nested' => [ 'path' => 'tags' ],
                        'aggs' => [
                            'hits' => [
                                'terms' => [
                                    'field' => 'tags.tag',
                                    'size' => 10
                                ]
                            ]
                        ]
                    ]
                ];
            break;
            case 'sentiment':
                unset($q['size']);

                $results = [];

                foreach ([
                    'twitter'         => [ 'index' => [ 'twitter',   'tweets',   '*' ], 'type' => 'tweet'    ],
                    'sozluk'          => [ 'index' => [ 'sozluk',    '*'             ], 'type' => 'entry'    ],
                    'news'            => [ 'index' => [ 'media',     '*'             ], 'type' => 'article'  ],
                    'blog'            => [ 'index' => [ 'blog',      '*'             ], 'type' => 'document' ],
                    'instagram'       => [ 'index' => [ 'instagram', 'medias',   '*' ], 'type' => 'media'    ],
                    'shopping'        => [ 'index' => [ 'shopping',  '*'             ], 'type' => 'product'  ],
                    'youtube_video'   => [ 'index' => [ 'youtube',   'videos'        ], 'type' => 'video'    ],
                    'youtube_comment' => [ 'index' => [ 'youtube',   'comments', '*' ], 'type' => 'comment'  ]
                ] as $key => $module)
                {
                    if (@in_array($key, $request->modules))
                    {
                        foreach ([ 'pos', 'neu', 'neg', 'hte' ] as $sntmnt)
                        {
                            $aggs[$key][$sntmnt] = [
                                'filter' => [ 'range' => [ implode('.', [ 'sentiment', $sntmnt ]) => [ 'gte' => 0.5 ] ] ]
                            ];
                        }
                    }
                }
            break;
            case 'consumer':
                unset($q['size']);

                $results = [];

                foreach ([
                    'twitter'         => [ 'index' => [ 'twitter',   'tweets',   '*' ], 'type' => 'tweet'   ],
                    'sozluk'          => [ 'index' => [ 'sozluk',    '*'             ], 'type' => 'entry'   ],
                    'instagram'       => [ 'index' => [ 'instagram', 'medias',   '*' ], 'type' => 'media'   ],
                    'youtube_video'   => [ 'index' => [ 'youtube',   'videos'        ], 'type' => 'video'   ],
                    'youtube_comment' => [ 'index' => [ 'youtube',   'comments', '*' ], 'type' => 'comment' ]
                ] as $key => $module)
                {
                    if (@in_array($key, $request->modules))
                    {
                        foreach ([ 'nws', 'req', 'que', 'cmp' ] as $cnsmr)
                        {
                            $aggs[$key][$cnsmr] = [
                                'filter' => [ 'range' => [ implode('.', [ 'consumer', $cnsmr ]) => [ 'gte' => 0.5 ] ] ]
                            ];
                        }
                    }
                }
            break;
            case 'gender':
                $aggs['twitter'] = [ 'gender' => [ 'terms' => [ 'field' => 'user.gender' ] ] ];
                $aggs['sozluk'] = [ 'gender' => [ 'terms' => [ 'field' => 'gender' ] ] ];
                $aggs['youtube_video'] = [ 'gender' => [ 'terms' => [ 'field' => 'channel.gender' ] ] ];
                $aggs['youtube_comment'] = [ 'gender' => [ 'terms' => [ 'field' => 'channel.gender' ] ] ];
            break;
        }

        $data['q'] = $q;

        return [
            'status' => 'ok',
            'data' => self::query($data, $aggs, $request)
        ];
    }

    /**
     * Modül Sorguları
     *
     * @return array
     */
    private static function query(array $data, array $aggregations, ArchiveAggregationRequest $request)
    {
        $modules = [];

        if (@$aggregations['all'])
        {
            $data['q']['aggs'] = $aggregations['all'];
        }

        $aggs = [];

        if ($data['organisation']->data_twitter && (@$aggregations['twitter'] || @$aggregations['all']) && @in_array('twitter', $request->modules))
        {
            $twitter_q = $data['q'];

            if ($data['request']->gender != 'all')
            {
                $twitter_q['query']['bool']['should'][] = [ 'match' => [ 'user.gender' => $data['request']->gender ] ];
                $twitter_q['query']['bool']['minimum_should_match'] = 1;
            }

            if ($request->sharp)
            {
                $twitter_q['query']['bool']['must_not'][] = [ 'match' => [ 'external.type' => 'retweet' ] ];
                $twitter_q['query']['bool']['must'][] = [ 'range' => [ 'counts.hashtag' => [ 'lte' => 2 ] ] ];
                $twitter_q['query']['bool']['must'][] = [ 'range' => [ 'illegal.nud' => [ 'lte' => 0.4 ] ] ];
                $twitter_q['query']['bool']['must'][] = [ 'range' => [ 'illegal.bet' => [ 'lte' => 0.4 ] ] ];
            }

            if (@$aggregations['twitter'])
            {
                $twitter_q['aggs'] = $aggregations['twitter'];
            }

            $query = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', $twitter_q);

            $aggs['twitter'] = @$query->data['aggregations'];
        }

        /***/

        if ($data['organisation']->data_instagram && (@$aggregations['instagram'] || @$aggregations['all']) && @in_array('instagram', $request->modules))
        {
            $instagram_q = $data['q'];

            if (@$aggregations['instagram'])
            {
                $instagram_q['aggs'] = $aggregations['instagram'];
            }

            $query = Document::search([ 'instagram', 'medias', '*' ], 'media', $instagram_q);

            $aggs['instagram'] = @$query->data['aggregations'];
        }

        /***/

        if ($data['organisation']->data_sozluk && (@$aggregations['sozluk'] || @$aggregations['all']) && @in_array('sozluk', $request->modules))
        {
            $sozluk_q = $data['q'];

            if (@$aggregations['sozluk'])
            {
                $sozluk_q['aggs'] = $aggregations['sozluk'];
            }

            if ($data['request']->gender != 'all')
            {
                $sozluk_q['query']['bool']['should'][] = [ 'match' => [ 'gender' => $data['request']->gender ] ];
                $sozluk_q['query']['bool']['minimum_should_match'] = 1;
            }

            $query = Document::search([ 'sozluk', '*' ], 'entry', $sozluk_q);

            $_aggs = @$query->data['aggregations'];

            $check = 0;

            if (@$_aggs['sites']['buckets'])
            {
                foreach ($_aggs['sites']['buckets'] as $key => $item)
                {
                    $aggs['sozluk']['sites'][] = [
                        'hit' => $item['doc_count'],
                        'name' => @SozlukCrawler::where('id', $item['key'])->value('name'),
                        'id' => $item['key']
                    ];
                }
            }
            else
            {
                $check++;
            }

            if (@$_aggs['users']['buckets'])
            {
                foreach ($_aggs['users']['buckets'] as $key => $item)
                {
                    $id = @$item['properties']['hits']['hits'][0]['_source']['site_id'];

                    $aggs['sozluk']['users'][] = [
                        'hit' => $item['doc_count'],
                        'name' => $item['key'],
                        'site' => @SozlukCrawler::where('id', intval($id))->value('name'),
                        'id' => intval($id)
                    ];
                }
            }
            else
            {
                $check++;
            }

            if (@$_aggs['topics']['buckets'])
            {
                foreach ($_aggs['topics']['buckets'] as $key => $item)
                {
                    $id = @$item['properties']['hits']['hits'][0]['_source']['site_id'];
                    $title = $item['properties']['hits']['hits'][0]['_source']['title'];

                    $aggs['sozluk']['topics'][] = [
                        'hit' => $item['doc_count'],
                        'name' => $item['key'],
                        'site' => @SozlukCrawler::where('id', intval($id))->value('name'),
                        'title' => $title
                    ];
                }
            }
            else
            {
                $check++;
            }

            if ($check == 3)
            {
                $aggs['sozluk'] = $_aggs;
            }
        }

        /***/

        if ($data['organisation']->data_news && (@$aggregations['news'] || @$aggregations['all']) && @in_array('news', $request->modules))
        {
            $media_q = $data['q'];

            if (@$aggregations['news'])
            {
                $media_q['aggs'] = $aggregations['news'];
            }

            $media_q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

            if ($request->state)
            {
                $media_q['query']['bool']['must'][] = [ 'match' => [ 'state' => $request->state ] ];
            }

            $query = Document::search([ 'media', 's*' ], 'article', $media_q);

            $_aggs = @$query->data['aggregations'];

            if (@$_aggs['sites']['buckets'])
            {
                foreach ($_aggs['sites']['buckets'] as $key => $item)
                {
                    $aggs['news']['sites'][] = [
                        'hit' => $item['doc_count'],
                        'name' => @MediaCrawler::where('id', $item['key'])->value('name'),
                        'id' => $item['key']
                    ];
                }
            }
            else
            {
                $aggs['news'] = $_aggs;
            }
        }

        /***/

        if ($data['organisation']->data_blog && (@$aggregations['blog'] || @$aggregations['all']) && @in_array('blog', $request->modules))
        {
            $blog_q = $data['q'];

            if (@$aggregations['blog'])
            {
                $blog_q['aggs'] = $aggregations['blog'];
            }

            $blog_q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

            $query = Document::search([ 'blog', 's*' ], 'document', $blog_q);

            $_aggs = @$query->data['aggregations'];

            if (@$_aggs['sites']['buckets'])
            {
                foreach ($_aggs['sites']['buckets'] as $key => $item)
                {
                    $aggs['blog']['sites'][] = [
                        'hit' => $item['doc_count'],
                        'name' => @BlogCrawler::where('id', $item['key'])->value('name'),
                        'id' => $item['key']
                    ];
                }
            }
            else
            {
                $aggs['blog'] = $_aggs;
            }
        }

        /***/

        if ($data['organisation']->data_youtube_video && (@$aggregations['youtube_video'] || @$aggregations['all']) && @in_array('youtube_video', $request->modules))
        {
            $youtube_video_q = $data['q'];

            if (@$aggregations['youtube_video'])
            {
                $youtube_video_q['aggs'] = $aggregations['youtube_video'];
            }

            $query = Document::search([ 'youtube', 'videos' ], 'video', $youtube_video_q);

            $aggs['youtube_video'] = @$query->data['aggregations'];
        }

        /***/

        if ($data['organisation']->data_youtube_comment && (@$aggregations['youtube_comment'] || @$aggregations['all']) && @in_array('youtube_comment', $request->modules))
        {
            $youtube_comment_q = $data['q'];

            if (@$aggregations['youtube_comment'])
            {
                $youtube_comment_q['aggs'] = $aggregations['youtube_comment'];
            }

            $query = Document::search([ 'youtube', 'comments', '*' ], 'comment', $youtube_comment_q);

            $aggs['youtube_comment'] = @$query->data['aggregations'];
        }

        /***/

        if ($data['organisation']->data_shopping && (@$aggregations['shopping'] || @$aggregations['all']) && @in_array('shopping', $request->modules))
        {
            $shopping_q = $data['q'];

            if (@$aggregations['shopping'])
            {
                $shopping_q['aggs'] = $aggregations['shopping'];
            }

            $shopping_q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

            $query = Document::search([ 'shopping', '*' ], 'product', $shopping_q);

            $_aggs = @$query->data['aggregations'];

            $check = 0;

            if (@$_aggs['sites']['buckets'])
            {
                foreach ($_aggs['sites']['buckets'] as $key => $item)
                {
                    $aggs['shopping']['sites'][] = [
                        'hit' => $item['doc_count'],
                        'name' => @ShoppingCrawler::where('id', $item['key'])->value('name'),
                        'id' => $item['key']
                    ];
                }
            }
            else
            {
                $check++;
            }

            if (@$_aggs['users']['buckets'])
            {
                foreach ($_aggs['users']['buckets'] as $key => $item)
                {
                    $id = @$item['properties']['hits']['hits'][0]['_source']['site_id'];

                    $aggs['shopping']['users'][] = [
                        'hit' => $item['doc_count'],
                        'name' => $item['key'],
                        'site' => @ShoppingCrawler::where('id', intval($id))->value('name'),
                        'id' => intval($id)
                    ];
                }
            }
            else
            {
                $check++;
            }

            if ($check == 2)
            {
                $aggs['shopping'] = $_aggs;
            }
        }

        return $aggs;
    }


    /**
     * Banner
     *
     * @return array
     */
    public static function banner(ArchiveAggregationRequest $request)
    {
        $data = [];
        $modules = [];

        $organisation = auth()->user()->organisation;

        $clean = Term::cleanSearchQuery($request->string);

        $q = [
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
                    ],
                    'must' => [
                        [ 'exists' => [ 'field' => 'created_at' ] ],
                        [
                            'query_string' => [
                                'fields' => [
                                    'title',
                                    'description',
                                    'entry',
                                    'text'
                                ],
                                'query' => $clean->line,
                                'default_operator' => 'AND'
                            ]
                        ]
                    ]
                ]
            ],
            'size' => 0
        ];

        foreach ($request->modules as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
                        $modules[] = 'tweet';
                    }
                break;
                case 'instagram':
                    if ($organisation->data_twitter)
                    {
                        $modules[] = 'media';
                    }
                break;
                case 'sozluk':
                    if ($organisation->data_sozluk)
                    {
                        $modules[] = 'entry';
                    }
                break;
                case 'news':
                    if ($organisation->data_news)
                    {
                        $modules[] = 'article';
                    }
                break;
                case 'blog':
                    if ($organisation->data_blog)
                    {
                        $modules[] = 'document';
                    }
                break;
                case 'youtube_video':
                    if ($organisation->data_youtube_video)
                    {
                        $modules[] = 'video';
                    }
                break;
                case 'youtube_comment':
                    if ($organisation->data_youtube_comment)
                    {
                        $modules[] = 'comment';
                    }
                break;
            }
        }

        switch ($request->type)
        {
            case 'sentiment':
                $aggs['sentiment_pos'] = [ 'sum' => [ 'field' => 'sentiment.pos' ] ];
                $aggs['sentiment_neg'] = [ 'sum' => [ 'field' => 'sentiment.neg' ] ];
                $aggs['sentiment_neu'] = [ 'sum' => [ 'field' => 'sentiment.neu' ] ];
                $aggs['sentiment_hte'] = [ 'sum' => [ 'field' => 'sentiment.hte' ] ];
            break;
            case 'consumer':
                $aggs['consumer_que'] = [ 'sum' => [ 'field' => 'consumer.req' ] ];
                $aggs['consumer_req'] = [ 'sum' => [ 'field' => 'consumer.que' ] ];
                $aggs['consumer_cmp'] = [ 'sum' => [ 'field' => 'consumer.nws' ] ];
                $aggs['consumer_nws'] = [ 'sum' => [ 'field' => 'consumer.cmp' ] ];
            break;
            case 'gender':
                $aggs['twitter'] = [ 'terms' => [ 'field' => 'user.gender' ] ];
                $aggs['sozluk'] = [ 'terms' => [ 'field' => 'gender' ] ];
                $aggs['youtube'] = [ 'terms' => [ 'field' => 'channel.gender' ] ];
                $aggs['shopping'] = [ 'terms' => [ 'field' => 'seller.gender' ] ];
            break;
            case 'hashtag':
                $aggs['hashtag'] = [
                    'nested' => [ 'path' => 'entities.hashtags' ],
                    'aggs' => [
                        'hits' => [
                            'terms' => [
                                'field' => 'entities.hashtags.hashtag',
                                'size' => 10
                            ]
                        ]
                    ]
                ];
            break;
        }

        $query = [
            'aggs' => Document::search([ '*' ], implode(',', $modules), array_merge($q, [ 'size' => 0, 'aggs' => $aggs ]))
        ];

        $data = [
            'status' => 'err'
        ];

        switch ($request->type)
        {
            case 'sentiment':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations'])
                    {
                        $clean_hits = $query['aggs']->data['aggregations'];

                        $data['data'] = [
                            'pos' => intval($clean_hits['sentiment_hte']['value']),
                            'neg' => intval($clean_hits['sentiment_neg']['value']),
                            'neu' => intval($clean_hits['sentiment_neu']['value']),
                            'hte' => intval($clean_hits['sentiment_hte']['value'])
                        ];
                    }
                }
            break;
            case 'consumer':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations'])
                    {
                        $clean_hits = $query['aggs']->data['aggregations'];
                        $total_hits = $clean_hits['consumer_req']['value'] + $clean_hits['consumer_que']['value'] + $clean_hits['consumer_cmp']['value'] + $clean_hits['consumer_nws']['value'];

                        $data['data'] = [
                            'que' => intval($clean_hits['consumer_que']['value']),
                            'req' => intval($clean_hits['consumer_req']['value']),
                            'cmp' => intval($clean_hits['consumer_cmp']['value']),
                            'nws' => intval($clean_hits['consumer_nws']['value'])
                        ];

                        foreach ([ 'que', 'req', 'cmp', 'nws' ] as $key => $item)
                        {
                            if ($data['data'][$item] >= 25)
                            {
                                $data['data'][$item] = $data['data'][$item]-25;
                            }
                            else
                            {
                                $data['data'][$item] = 0;
                            }
                        }
                    }
                }
            break;
            case 'gender':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations'])
                    {
                        $data['data']['male'] = 0;
                        $data['data']['female'] = 0;
                        $data['data']['unknown'] = 0;

                        foreach ([ 'twitter', 'sozluk', 'youtube', 'shopping' ] as $item)
                        {
                            $clean_hits = $query['aggs']->data['aggregations'][$item]['buckets'];

                            if ($clean_hits)
                            {
                                foreach ($clean_hits as $bucket)
                                {
                                    switch ($bucket['key'])
                                    {
                                        case 'male':
                                            $data['data']['male'] = $data['data']['male'] + $bucket['doc_count'];
                                        break;
                                        case 'female':
                                            $data['data']['female'] = $data['data']['female'] + $bucket['doc_count'];
                                        break;
                                        case 'unknown':
                                            $data['data']['unknown'] = $data['data']['unknown'] + $bucket['doc_count'];
                                        break;
                                    }
                                }
                            }
                        }

                        $total_hits = $data['data']['male'] + $data['data']['female'] + $data['data']['unknown'];

                        $data['data']['male'] = round(($data['data']['male'] ? $data['data']['male']*100/$total_hits : 0), 2);
                        $data['data']['female'] = round(($data['data']['female'] ? $data['data']['female']*100/$total_hits : 0), 2);
                        $data['data']['unknown'] = round(($data['data']['unknown'] ? $data['data']['unknown']*100/$total_hits : 0), 2);
                    }
                }
            break;
            case 'hashtag':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations'])
                    {
                        if ($query['aggs']->data['aggregations']['hashtag']['doc_count'])
                        {
                            foreach ($query['aggs']->data['aggregations']['hashtag']['hits']['buckets'] as $key => $row)
                            {
                                $data['data'][$row['key']] = $row['doc_count'];
                            }
                        }
                    }
                }
            break;
        }

        return $data;
    }
}
