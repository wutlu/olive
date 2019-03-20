<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Elasticsearch\Document;

use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\ShoppingCrawler;

use Carbon\Carbon;

use App\Utilities\Term;

use App\Http\Requests\SearchRequest;

class ContentController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth');

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyonu Olanlar
         */
        $this->middleware('organisation:have');

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyon Onayı
         */
        $this->middleware('can:organisation-status');
    }

    /**
     * Modül Ayraç
     *
     * @return view
     */
    public static function module(string $es_index, string $es_type, string $es_id)
    {
        $document = Document::get($es_index, $es_type, $es_id);

        if ($document->status == 'ok')
        {
            $data = [];
            $document = $document->data;

            switch ($es_type)
            {
                case 'entry':
                    $crawler = SozlukCrawler::where('id', $document['_source']['site_id'])->firstOrFail();

                    $site = [
                        [ 'match' => [ 'group_name' => $document['_source']['group_name'] ] ]
                    ];

                    $data = [
                        'total' => Document::search($es_index, 'entry', [
                            'query' => [
                                'bool' => [
                                    'must' => $site
                                ]
                            ],
                            'aggs' => [
                                'positive' => [ 'avg' => [ 'field' => 'sentiment.pos' ] ],
                                'neutral' => [ 'avg' => [ 'field' => 'sentiment.neu' ] ],
                                'negative' => [ 'avg' => [ 'field' => 'sentiment.neg' ] ],
                                'popular_keywords' => [
                                    'terms' => [
                                        'field' => 'entry',
                                        'size' => 100
                                    ]
                                ]
                            ],
                            'size' => 0
                        ])
                    ];

                    if (strpos($document['_source']['url'], 'eksisozluk.com/'))
                    {
                        $data['slug'] = 'eksi';
                    }
                    elseif (strpos($document['_source']['url'], 'instela.com/'))
                    {
                        $data['slug'] = 'instela';
                    }
                    elseif (strpos($document['_source']['url'], 'incisozluk.com.tr/'))
                    {
                        $data['slug'] = 'inci';
                    }
                    elseif (strpos($document['_source']['url'], 'uludagsozluk.com/'))
                    {
                        $data['slug'] = 'uludag';
                    }

                    $bucket = @$data['total']->data['aggregations']['popular_keywords']['buckets'];

                    if ($bucket)
                    {
                        $bucket = implode(' ', array_map(function($a) {
                            return $a['key'];
                        }, $bucket));

                        $data['keywords'] = Term::commonWords($bucket, 100);
                    }

                    $title = implode(' ', [ $crawler->name, '/', $document['_source']['title'] ]);
                break;

                case 'product':
                    $crawler = ShoppingCrawler::where('id', $document['_source']['site_id'])->firstOrFail();

                    $site = [
                        [ 'match' => [ 'site_id' => $crawler->id ] ],
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ];

                    $title = implode(' ', [ $crawler->name, '/', '#'.$document['_source']['id'] ]);
                break;

                case 'tweet':
                    $title = implode(' / ', [ 'Twitter', $document['_source']['user']['name'], '#'.$es_id ]);

                    $user = [
                        [ 'match' => [ 'user.id' => $document['_source']['user']['id'] ] ]
                    ];

                    $data = [
                        'total' => Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
                            'query' => [
                                'bool' => [
                                    'must' => $user
                                ]
                            ],
                            'aggs' => [
                                'positive' => [ 'avg' => [ 'field' => 'sentiment.pos' ] ],
                                'neutral' => [ 'avg' => [ 'field' => 'sentiment.neu' ] ],
                                'negative' => [ 'avg' => [ 'field' => 'sentiment.neg' ] ]
                            ],
                            'size' => 0
                        ]),
                        'retweet' => Document::count([ 'twitter', 'tweets', '*' ], 'tweet', [
                            'query' => [
                                'bool' => [
                                    'must' => [
                                        [ 'match' => [ 'external.id' => $document['_source']['id'] ] ],
                                        [ 'match' => [ 'external.type' => 'retweet' ] ]
                                    ]
                                ]
                            ]
                        ]),
                        'reply' => Document::count([ 'twitter', 'tweets', '*' ], 'tweet', [
                            'query' => [
                                'bool' => [
                                    'must' => [
                                        [ 'match' => [ 'external.id' => $document['_source']['id'] ] ]
                                    ],
                                    'must_not' => [
                                        [ 'match' => [ 'external.type' => 'retweet' ] ]
                                    ]
                                ]
                            ]
                        ])
                    ];

                    if (@$document['_source']['external']['id'])
                    {
                        $external = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [ 'query' => [ 'match' => [ 'id' => $document['_source']['external']['id'] ] ] ]);

                        $data['external'] = @$external->data['hits']['hits'][0];
                    }

                    $follow_graph = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
                        'size' => 100,
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'user.id' => $document['_source']['user']['id'] ] ]
                                ]
                            ]
                        ],
                        '_source' => [
                            'user.counts.friends',
                            'user.counts.followers',
                            'user.counts.statuses',
                            'user.counts.listed',
                            'user.counts.favourites',
                            'created_at'
                        ],
                        'sort' => [
                             'created_at' => 'DESC'
                        ]
                    ]);

                    if (@$follow_graph->data['hits']['hits'])
                    {
                        $stats = [];

                        $_created_at = null;
                        $_followers = null;
                        $_friends = null;
                        $_statuses = null;
                        $_listed = null;
                        $_favourites = null;

                        foreach (array_reverse($follow_graph->data['hits']['hits']) as $arr)
                        {
                            $created_at = date('Y.m.d', strtotime($arr['_source']['created_at']));

                            if ($created_at != $_created_at)
                            {
                                $followers = $arr['_source']['user']['counts']['followers'];
                                $friends = $arr['_source']['user']['counts']['friends'];
                                $statuses = $arr['_source']['user']['counts']['statuses'];
                                $listed = $arr['_source']['user']['counts']['listed'];
                                $favourites = $arr['_source']['user']['counts']['favourites'];

                                $stats[] = [
                                    'created_at' => $created_at,
                                    'followers' => $followers,
                                    'friends' => $friends,
                                    'statuses' => $statuses,
                                    'listed' => $listed,
                                    'favourites' => $favourites,

                                    'diff' => [
                                        'followers'  => $followers  < $_followers  ? 'red' : ($followers  == $_followers  ? 'grey' : 'green'),
                                        'friends'    => $friends    < $_friends    ? 'red' : ($friends    == $_friends    ? 'grey' : 'green'),
                                        'statuses'   => $statuses   < $_statuses   ? 'red' : ($statuses   == $_statuses   ? 'grey' : 'green'),
                                        'listed'     => $listed     < $_listed     ? 'red' : ($listed     == $_listed     ? 'grey' : 'green'),
                                        'favourites' => $favourites < $_favourites ? 'red' : ($favourites == $_favourites ? 'grey' : 'green'),

                                        '_followers'  => $followers  - $_followers,
                                        '_friends'    => $friends    - $_friends,
                                        '_statuses'   => $statuses   - $_statuses,
                                        '_listed'     => $listed     - $_listed,
                                        '_favourites' => $favourites - $_favourites,
                                    ]
                                ];

                                $_followers = $followers;
                                $_friends = $friends;
                                $_statuses = $statuses;
                                $_listed = $listed;
                                $_favourites = $favourites;
                            }

                            $_created_at = $created_at;
                        }

                        $data['stats'] = array_reverse($stats);

                        $_followers  = array_map(function($arr) { return $arr['diff']['_followers'];  }, $data['stats']);
                        $_friends    = array_map(function($arr) { return $arr['diff']['_friends'];    }, $data['stats']);
                        $_statuses   = array_map(function($arr) { return $arr['diff']['_statuses'];   }, $data['stats']);
                        $_listed     = array_map(function($arr) { return $arr['diff']['_listed'];     }, $data['stats']);
                        $_favourites = array_map(function($arr) { return $arr['diff']['_favourites']; }, $data['stats']);

                        array_pop($_followers);
                        array_pop($_friends);
                        array_pop($_statuses);
                        array_pop($_listed);
                        array_pop($_favourites);

                        $data['statistics']['diff']['_followers']  = $_followers;
                        $data['statistics']['diff']['_friends']    = $_friends;
                        $data['statistics']['diff']['_statuses']   = $_statuses;
                        $data['statistics']['diff']['_listed']     = $_listed;
                        $data['statistics']['diff']['_favourites'] = $_favourites;
                    }
                break;

                case 'video':
                    $title = implode(' / ', [ 'YouTube', 'Video', '#'.$es_id ]);
                break;

                case 'comment':
                    $title = implode(' / ', [ 'YouTube', 'Yorum', '#'.$es_id ]);
                break;

                case 'article':
                    $crawler = MediaCrawler::where('id', $document['_source']['site_id'])->firstOrFail();

                    $site = [
                        [ 'match' => [ 'site_id' => $crawler->id ] ],
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ];

                    $data = [
                        'crawler' => $crawler,
                        'total' => Document::search($es_index, 'article', [
                            'query' => [
                                'bool' => [
                                    'must' => $site
                                ]
                            ],
                            'aggs' => [
                                'positive' => [ 'avg' => [ 'field' => 'sentiment.pos' ] ],
                                'neutral' => [ 'avg' => [ 'field' => 'sentiment.neu' ] ],
                                'negative' => [ 'avg' => [ 'field' => 'sentiment.neg' ] ],
                                'popular_keywords' => [
                                    'terms' => [
                                        'field' => 'description',
                                        'size' => 100
                                    ]
                                ]
                            ],
                            'size' => 0
                        ])
                    ];

                    $bucket = @$data['total']->data['aggregations']['popular_keywords']['buckets'];

                    if ($bucket)
                    {
                        $bucket = implode(' ', array_map(function($a) {
                            return $a['key'];
                        }, $bucket));

                        $data['keywords'] = Term::commonWords($bucket, 100);
                    }

                    $title = $crawler->name . ' / ' . '#'.$document['_source']['id'];
                break;

                default: abort(404); break;
            }

            $es = (object) [
                'index' => $es_index,
                'type' => $es_type,
                'id' => $es_id
            ];

            return view(implode('.', [ 'content', $es_type ]), compact('document', 'title', 'es', 'data'));
        }
        else
        {
            return abort(404);
        }
    }

    /**
     * tweet Aggregations
     *
     * @return array
     */
    public static function tweetAggregation(string $type, int $user_id)
    {
        $data = [
            'query' => ($type == 'mention_in') ? [
                'nested' => [
                    'path' => 'entities.mentions',
                    'query' => [
                        'bool' => [
                            'must' => [
                                [ 'match' => [ 'entities.mentions.mention.id' => $user_id ] ]
                            ]
                        ]
                    ]
                ]
            ] : [
                'bool' => [
                    'must' => [
                        [ 'match' => [ 'user.id' => $user_id ] ]
                    ]
                ]
            ],
            'size' => 0
        ];

        switch ($type)
        {
            case 'names':
                $data['aggs']['names'] = [
                    'terms' => [
                        'field' => 'user.name'
                    ]
                ];
            break;
            case 'screen_names':
                $data['aggs']['screen_names'] = [
                    'terms' => [
                        'field' => 'user.screen_name'
                    ]
                ];
            break;
            case 'platforms':
                $data['aggs']['platforms'] = [
                    'terms' => [
                        'field' => 'platform'
                    ]
                ];
            break;
            case 'langs':
                $data['aggs']['langs'] = [
                    'terms' => [
                        'field' => 'lang'
                    ]
                ];
            break;
            case 'places':
                $data['aggs']['places'] = [
                    'terms' => [
                        'field' => 'place.full_name'
                    ]
                ];
            break;
            case 'mention_out':
                $data['aggs']['mention_out'] = [
                    'nested' => [ 'path' => 'entities.mentions' ],
                    'aggs' => [
                        'hit_items' => [
                            'terms' => [
                                'field' => 'entities.mentions.mention.screen_name',
                                'size' => 15
                            ]
                        ]
                    ]
                ];
            break;
            case 'hashtags':
                $data['aggs']['hashtags'] = [
                    'nested' => [ 'path' => 'entities.hashtags' ],
                    'aggs' => [
                        'hit_items' => [
                            'terms' => [
                                'field' => 'entities.hashtags.hashtag',
                                'size' => 15
                            ]
                        ]
                    ]
                ];
            break;
            case 'urls':
                $data['aggs']['urls'] = [
                    'nested' => [ 'path' => 'entities.urls' ],
                    'aggs' => [
                        'hit_items' => [
                            'terms' => [
                                'field' => 'entities.urls.url',
                                'size' => 15
                            ]
                        ]
                    ]
                ];
            break;
            case 'mention_in':
                $data['aggs']['mention_in'] = [
                    'terms' => [
                        'field' => 'user.screen_name',
                        'size' => 15
                    ]
                ];
            break;
        }

        $data = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', $data);

        switch ($type)
        {
            case 'mention_out':
            case 'hashtags':
            case 'urls':
                $data = $data->data['aggregations'][$type]['hit_items']['buckets'];
            break;
            default:
                $data = $data->data['aggregations'][$type]['buckets'];
            break;
        }

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    /**
     * video Aggregations
     *
     * @return array
     */
    public static function videoAggregation(string $type, string $channel_id)
    {
        $data = [
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'match' => [ 'channel.id' => $channel_id ] ]
                    ]
                ]
            ],
            'size' => 0
        ];

        switch ($type)
        {
            case 'titles': $data['aggs']['titles'] = [ 'terms' => [ 'field' => 'channel.title' ] ]; break;
        }

        $query = Document::search([ 'youtube', '*' ], 'video,comment', $data);

        $data = $query->data['aggregations'][$type]['buckets'];

        return [
            'status' => 'ok',
            'data' => $data
        ];
    }

    /**
     * Histogram
     *
     * @return array
     */
    public static function histogram(string $type, string $period, string $es_id, string $es_index_key = 'xxx')
    {
        switch ($period)
        {
            case 'hourly':
                $script = 'doc.created_at.value.getHourOfDay()';
            break;
            case 'daily':
                $script = 'doc.created_at.value.getDayOfWeek()';
            break;
        }

        $arr = [
            'size' => 0,
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'exists' => [ 'field' => 'created_at' ] ]
                    ]
                ]
            ],
            'aggs' => [
                'results' => [
                    'histogram' => [
                        'script' => $script,
                        'interval' => 1
                    ]
                ]
            ]
        ];

        switch ($type)
        {
            case 'video-comments':
                $arr['query']['bool']['must'][] = [
                    'match' => [
                        'video_id' => $es_id
                    ]
                ];

                $document = Document::search([ 'youtube', 'comments', '*' ], 'comment', $arr);
            break;
            case 'video-by-video':
            case 'comment-by-video':
                $doc = Document::get([ 'youtube', 'videos' ], 'video', $es_id);

                $arr['query']['bool']['must'][] = [
                    'match' => [
                        'channel.id' => $doc->data['_source']['channel']['id']
                    ]
                ];

                switch ($type)
                {
                    case 'video-by-video':
                        $es_index = [ 'youtube', 'videos' ];
                        $es_type = 'video';
                    break;
                    case 'comment-by-video':
                        $es_index = [ 'youtube', 'comments', '*' ];
                        $es_type = 'comment';
                    break;
                }

                $document = Document::search($es_index, $es_type, $arr);
            break;
            case 'entry':
                $doc = Document::get([ 'sozluk', $es_index_key ], 'entry', $es_id);

                if ($doc->status == 'ok')
                {
                    $arr['query']['bool']['must'][] = [
                        'match' => [ 'group_name' => $doc->data['_source']['group_name'] ]
                    ];

                    $document = Document::search([ 'sozluk', $es_index_key ], 'entry', $arr);
                }
            break;
            case 'article':
                $arr['query']['bool']['must'][] = [
                    'match' => [ 'site_id' => $es_id ]
                ];

                $document = Document::search([ 'media', $es_index_key ], 'article', $arr);
            break;
            case 'product':
                $doc = Document::get([ 'shopping', $es_index_key ], 'product', $es_id);

                $arr['query']['bool']['must'][] = [
                    'match' => [ 'status' => 'ok' ]
                ];
                $arr['query']['bool']['must'][] = [
                    'nested' => [
                        'path' => 'breadcrumb',
                        'query' => [
                            'query_string' => [
                                'fields' => [
                                    'breadcrumb.segment'
                                ],
                                'query' => implode(' || ', array_map(function($arr) { return $arr['segment']; }, $doc->data['_source']['breadcrumb'])),
                                'default_operator' => 'OR'
                            ]
                        ]
                    ]
                ];

                $arr['min_score'] = 4;

                $document = Document::search([ 'shopping', '*' ], 'product', $arr);
            break;
            case 'tweet':
                $doc = Document::get([ 'twitter', 'tweets', $es_index_key ], 'tweet', $es_id);

                if ($doc->status = 'ok')
                {
                    $arr['query']['bool']['must'][] = [
                        'match' => [
                            'user.id' => $doc->data['_source']['user']['id']
                        ]
                    ];

                    $document = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', $arr);
                }
            break;
            default: return abort(404); break;
        }

        return [
            'status' => 'ok',
            'data' => @$document->data['aggregations']['results']
        ];
    }

    /**
     * Benzer İçerikler
     *
     * @return array
     */
    public static function smilar(string $es_index, string $es_type, string $es_id, string $type = '', SearchRequest $request)
    {
        $document = Document::get($es_index, $es_type, $es_id);

        if ($document->status == 'ok')
        {
            switch ($es_type)
            {
                case 'tweet':
                    $arr = [
                        'from' => $request->skip,
                        'size' => $request->take,
                        '_source' => [ 'id', 'user.id', 'user.name', 'user.screen_name', 'text', 'created_at' ],
                        'sort' => [
                            'created_at' => 'DESC'
                        ]
                    ];

                    if ($type == 'retweet')
                    {
                        $arr['query']['bool']['must'][] = [
                            'match' => [ 'external.id' => $es_id ]
                        ];
                        $arr['query']['bool']['must_not'][] = [
                            'match' => [ 'external.type' => 'retweet' ]
                        ];
                    }
                    else
                    {
                        $arr['query']['bool']['must'][] = [
                            'match' => [ 'user.id' => $document->data['_source']['user']['id'] ]
                        ];
                    }

                    $documents = Document::search([ 'twitter', 'tweets', '*' ], $es_type, $arr);
                break;
                default:
                    $smilar = Term::commonWords($document->data['_source']['title']);

                    if ($smilar)
                    {
                        switch ($es_type)
                        {
                            case 'article':
                                $documents = Document::search([ 'media', '*' ], $es_type, [
                                    'query' => [
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'match' => [ 'status' => 'ok' ]
                                                ],
                                                [
                                                    'more_like_this' => [
                                                        'fields' => [ 'title' ],
                                                        'like' => array_keys($smilar),
                                                        'min_term_freq' => 1,
                                                        'min_doc_freq' => 1
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    'min_score' => 10,
                                    'from' => $request->skip,
                                    'size' => $request->take,
                                    '_source' => [ 'url', 'title', 'description', 'created_at' ]
                                ]);
                            break;
                            case 'entry':
                                $documents = Document::search([ 'sozluk', '*' ], $es_type, [
                                    'query' => [
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'more_like_this' => [
                                                        'fields' => [ 'title' ],
                                                        'like' => array_keys($smilar),
                                                        'min_term_freq' => 1,
                                                        'min_doc_freq' => 1
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    'min_score' => 10,
                                    'from' => $request->skip,
                                    'size' => $request->take,
                                    '_source' => [ 'url', 'title', 'entry', 'author', 'created_at' ]
                                ]);
                            break;
                            case 'product':
                                $documents = Document::search([ 'shopping', '*' ], $es_type, [
                                    'query' => [
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'nested' => [
                                                        'path' => 'breadcrumb',
                                                        'query' => [
                                                            'query_string' => [
                                                                'fields' => [
                                                                    'breadcrumb.segment'
                                                                ],
                                                                'query' => implode(' || ', array_map(function($arr) { return $arr['segment']; }, $document->data['_source']['breadcrumb'])),
                                                                'default_operator' => 'OR'
                                                            ]
                                                        ]
                                                    ]
                                                ],
                                                [ 'match' => [ 'status' => 'ok' ] ]
                                            ]
                                        ]
                                    ],
                                    'min_score' => 3,
                                    'from' => $request->skip,
                                    'size' => $request->take,
                                    '_source' => [
                                        'url',
                                        'title',
                                        'description',
                                        'price',
                                        'breadcrumb',
                                        'created_at'
                                    ]
                                ]);
                            break;
                        }
                    }
                    else
                    {
                        return [
                            'status' => 'ok',
                            'hits' => []
                        ];
                    }
                break;
            }

            return [
                'status' => 'ok',
                'hits' => $documents->data['hits']['hits']
            ];
        }
        else
        {
            return [
                'status' => 'err',
                'data' => [
                    'reason' => 'not found'
                ]
            ];
        }
    }
}
