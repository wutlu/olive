<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Elasticsearch\Document;
use App\Elasticsearch\Indices;

use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\ShoppingCrawler;
use App\Models\Crawlers\BlogCrawler;

use Carbon\Carbon;

use App\Utilities\Term;

use App\Http\Requests\SearchRequest;

use System;

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
     ********************
     ******* ROOT *******
     ********************
     *
     * İçerik Sil
     *
     * @return array
     */
    public static function delete(string $es_index, string $es_type, string $es_id)
    {
        System::log(auth()->user()->name.' tarafından bir içerik silindi!',
            'App\Http\Controllers\ContentController::delete('.$es_index.', '.$es_type.', '.$es_id.')',
            10
        );

        return json_encode(Document::delete($es_index, $es_type, $es_id));
    }

    /**
     * Modül Ayraç
     *
     * @return view
     */
    public static function module(string $es_index, string $es_type, string $es_id)
    {
        $organisation = auth()->user()->organisation;
        $days = $organisation->historical_days;

        $document = Document::get($es_index, $es_type, $es_id);

        if ($document->status == 'ok')
        {
            $data = [];
            $document = $document->data;

            switch ($es_type)
            {
                case 'entry':
                    if (!$organisation->data_sozluk)
                    {
                        return abort(403);
                    }

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
                                'popular_keywords' => [
                                    'terms' => [
                                        'field' => 'entry',
                                        'size' => 50
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
                        $_temp_data = [];

                        foreach ($bucket as $item)
                        {
                            if (strlen($item['key']) > 2)
                            {
                                $_temp_data[$item['key']] = $item['doc_count'];
                            }
                        }

                        $data['keywords'] = $_temp_data;
                    }

                    $title = implode(' ', [ $crawler->name, '/', $document['_source']['title'] ]);
                break;
                case 'product':
                    if (!$organisation->data_shopping)
                    {
                        return abort(403);
                    }

                    $crawler = ShoppingCrawler::where('id', $document['_source']['site_id'])->firstOrFail();

                    $site = [
                        [ 'match' => [ 'site_id' => $crawler->id ] ],
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ];

                    $title = implode(' ', [ $crawler->name, '/', '#'.$document['_source']['id'] ]);
                break;
                case 'tweet':
                    if (!$organisation->data_twitter)
                    {
                        return abort(403);
                    }

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
                        'quote' => Document::count([ 'twitter', 'tweets', '*' ], 'tweet', [
                            'query' => [
                                'bool' => [
                                    'must' => [
                                        [ 'match' => [ 'external.id' => $document['_source']['id'] ] ],
                                        [ 'match' => [ 'external.type' => 'quote' ] ]
                                    ]
                                ]
                            ]
                        ]),
                        'reply' => Document::count([ 'twitter', 'tweets', '*' ], 'tweet', [
                            'query' => [
                                'bool' => [
                                    'must' => [
                                        [ 'match' => [ 'external.id' => $document['_source']['id'] ] ],
                                        [ 'match' => [ 'external.type' => 'reply' ] ]
                                    ]
                                ]
                            ]
                        ]),
                        'deleted' => Document::count([ 'twitter', 'tweets', '*' ], 'tweet', [
                            'query' => [
                                'bool' => [
                                    'must' => [
                                        [ 'match' => [ 'user.id' => $document['_source']['user']['id'] ] ],
                                        [ 'exists' => [ 'field' => 'deleted_at' ] ]
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
                    if (!$organisation->data_youtube_video)
                    {
                        return abort(403);
                    }

                    $title = implode(' / ', [ 'YouTube', 'Video', '#'.$es_id ]);

                    $channel = [
                        [ 'match' => [ 'channel.id' => $document['_source']['channel']['id'] ] ]
                    ];

                    $data = [
                        'total' => Document::search([ 'youtube', 'comments', '*' ], 'comment', [
                            'query' => [
                                'bool' => [
                                    'must' => $channel
                                ]
                            ],
                            'size' => 0
                        ])
                    ];
                break;
                case 'comment':
                    if (!$organisation->data_youtube_comment)
                    {
                        return abort(403);
                    }

                    $channel = [
                        [ 'match' => [ 'channel.id' => $document['_source']['channel']['id'] ] ]
                    ];

                    $data = [
                        'total' => Document::search([ 'youtube', 'comments', '*' ], 'comment', [
                            'query' => [
                                'bool' => [
                                    'must' => $channel
                                ]
                            ],
                            'size' => 0
                        ]),
                        'video_index' => Indices::name([ 'youtube', 'videos' ])
                    ];

                    $title = implode(' / ', [ 'YouTube', 'Yorum', $document['_source']['channel']['title'] ]);
                break;
                case 'article':
                    if (!$organisation->data_news)
                    {
                        return abort(403);
                    }

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
                                'popular_keywords' => [
                                    'terms' => [
                                        'field' => 'description',
                                        'size' => 50
                                    ]
                                ]
                            ],
                            'size' => 0
                        ])
                    ];

                    $bucket = @$data['total']->data['aggregations']['popular_keywords']['buckets'];

                    if ($bucket)
                    {
                        $_temp_data = [];

                        foreach ($bucket as $item)
                        {
                            if (strlen($item['key']) > 2)
                            {
                                $_temp_data[$item['key']] = $item['doc_count'];
                            }
                        }

                        $data['keywords'] = $_temp_data;
                    }

                    $title = $crawler->name . ' / ' . '#'.$document['_source']['id'];
                break;
                case 'document':
                    if (!$organisation->data_news)
                    {
                        return abort(403);
                    }

                    $crawler = BlogCrawler::where('id', $document['_source']['site_id'])->firstOrFail();

                    $site = [
                        [ 'match' => [ 'site_id' => $crawler->id ] ],
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ];

                    $data = [
                        'crawler' => $crawler,
                        'total' => Document::search($es_index, 'document', [
                            'query' => [
                                'bool' => [
                                    'must' => $site
                                ]
                            ],
                            'aggs' => [
                                'popular_keywords' => [
                                    'terms' => [
                                        'field' => 'description',
                                        'size' => 50
                                    ]
                                ]
                            ],
                            'size' => 0
                        ])
                    ];

                    $bucket = @$data['total']->data['aggregations']['popular_keywords']['buckets'];

                    if ($bucket)
                    {
                        $_temp_data = [];

                        foreach ($bucket as $item)
                        {
                            if (strlen($item['key']) > 2)
                            {
                                $_temp_data[$item['key']] = $item['doc_count'];
                            }
                        }

                        $data['keywords'] = $_temp_data;
                    }

                    $title = $crawler->name . ' / ' . '#'.$document['_source']['id'];
                break;
                case 'media':
                    if (!$organisation->data_instagram)
                    {
                        return abort(403);
                    }

                    $user = Document::get([ 'instagram', 'users' ], 'user', $document['_source']['user']['id']);

                    if ($user->status == 'ok')
                    {
                        $called_at = date('Y-m-d H:i:s', strtotime($user->data['_source']['called_at']));

                        if ($called_at <= date('Y-m-d H:i:s', strtotime('-2 days')))
                        {
                            return view('content.media_loading', compact('document'));
                        }
                    }
                    else
                    {
                        return view('content.media_loading', compact('document'));
                    }

                    $data['user'] = $user->data['_source'];

                    $title = implode(' / ', [ 'Instagram', 'Medya', '#'.$es_id ]);
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
        $days = auth()->user()->organisation->historical_days;

        if ($type == 'mention_in')
        {
            $data = [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'range' => [
                                    'created_at' => [
                                        'format' => 'YYYY-MM-dd',
                                        'gte' => date('Y-m-d', strtotime('-'.$days.' days'))
                                    ]
                                ]
                            ],
                            [
                                'nested' => [
                                    'path' => 'entities.mentions',
                                    'query' => [
                                        'bool' => [
                                            'filter' => [
                                                [
                                                    'match' => [
                                                        'entities.mentions.mention.id' => $user_id
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'size' => 0
            ];
        }
        else
        {
            $data = [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'range' => [
                                    'created_at' => [
                                        'format' => 'YYYY-MM-dd',
                                        'gte' => date('Y-m-d', strtotime('-'.$days.' days'))
                                    ]
                                ]
                            ],
                            [ 'match' => [ 'user.id' => $user_id ] ]
                        ]
                    ]
                ],
                'size' => 0
            ];
        }

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
     * media Aggregations
     *
     * @return array
     */
    public static function mediaAggregation(string $type, int $user_id)
    {
        $days = auth()->user()->organisation->historical_days;

        $user = Document::get([ 'instagram', 'users' ], 'user', $user_id);
        $user = $user->data['_source'];

        if ($type == 'mention_in')
        {
            $data = [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'range' => [
                                    'created_at' => [
                                        'format' => 'YYYY-MM-dd',
                                        'gte' => date('Y-m-d', strtotime('-'.$days.' days'))
                                    ]
                                ]
                            ],
                            [
                                'nested' => [
                                    'path' => 'entities.mentions',
                                    'query' => [
                                        'bool' => [
                                            'filter' => [
                                                [
                                                    'match' => [
                                                        'entities.mentions.mention.screen_name' => $user['screen_name']
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'size' => 0
            ];
        }
        else
        {
            $data = [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'range' => [
                                    'created_at' => [
                                        'format' => 'YYYY-MM-dd',
                                        'gte' => date('Y-m-d', strtotime('-'.$days.' days'))
                                    ]
                                ]
                            ],
                            [ 'match' => [ 'user.id' => $user['id'] ] ]
                        ]
                    ]
                ],
                'size' => 0
            ];
        }

        switch ($type)
        {
            case 'places':
                $data['aggs']['places'] = [
                    'terms' => [
                        'field' => 'place.name'
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
            case 'mention_in':
                $data['aggs']['mention_in'] = [
                    'terms' => [
                        'field' => 'user.id',
                        'size' => 15
                    ]
                ];
            break;
        }

        $data = Document::search([ 'instagram', 'medias', '*' ], 'media', $data);

        switch ($type)
        {
            case 'mention_out':
            case 'hashtags':
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
        $days = auth()->user()->organisation->historical_days;

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
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd',
                                    'gte' => date('Y-m-d', strtotime('-'.$days.' days'))
                                ]
                            ]
                        ]
                    ],
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
            case 'comment-by-comment':
            case 'video-by-comment':
                $doc = Document::get($es_index_key, 'comment', $es_id);

                $arr['query']['bool']['must'][] = [
                    'match' => [
                        'channel.id' => $doc->data['_source']['channel']['id']
                    ]
                ];

                switch ($type)
                {
                    case 'comment-by-comment':
                        $es_index = [ 'youtube', 'comments', '*' ];
                        $es_type = 'comment';
                    break;
                    case 'video-by-comment':
                        $es_index = [ 'youtube', 'videos' ];
                        $es_type = 'video';
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
            case 'document':
                $arr['query']['bool']['must'][] = [
                    'match' => [ 'site_id' => $es_id ]
                ];

                $document = Document::search([ 'blog', $es_index_key ], 'document', $arr);
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

                if ($doc->status == 'ok')
                {
                    $arr['query']['bool']['must'][] = [
                        'match' => [
                            'user.id' => $doc->data['_source']['user']['id']
                        ]
                    ];

                    $document = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', $arr);
                }
            break;
            case 'media':
                $doc = Document::get([ 'instagram', 'medias', $es_index_key ], 'media', $es_id);

                if ($doc->status == 'ok')
                {
                    $arr['query']['bool']['must'][] = [
                        'match' => [
                            'user.id' => $doc->data['_source']['user']['id']
                        ]
                    ];

                    $document = Document::search([ 'instagram', 'medias', '*' ], 'media', $arr);
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

        $clean = Term::cleanSearchQuery($request->string);

        if ($document->status == 'ok')
        {
            switch ($es_type)
            {
                case 'tweet':
                    $arr = [
                        'from' => $request->skip,
                        'size' => $request->take,
                        '_source' => [
                            'id',
                            'user.id',
                            'user.name',
                            'user.screen_name',
                            'user.image',
                            'user.verified',
                            'text',
                            'created_at',
                            'deleted_at',
                            'sentiment'
                        ],
                        'sort' => [
                            'created_at' => 'DESC'
                        ]
                    ];

                    if ($type == 'retweet' || $type == 'quote' || $type == 'reply')
                    {
                        $arr['query']['bool']['must'][] = [ 'match' => [ 'external.id' => $es_id ] ];
                        $arr['query']['bool']['must'][] = [ 'match' => [ 'external.type' => $type ] ];
                    }
                    else if ($type == 'deleted')
                    {
                        $arr['query']['bool']['must'][] = [
                            'match' => [ 'user.id' => $document->data['_source']['user']['id'] ]
                        ];
                        $arr['query']['bool']['must'][] = [
                            'exists' => [ 'field' => 'deleted_at' ]
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
                                    '_source' => [
                                        'url',
                                        'title',
                                        'description',
                                        'created_at',
                                        'deleted_at',
                                        'sentiment'
                                    ]
                                ]);
                            break;
                            case 'document':
                                $documents = Document::search([ 'blog', '*' ], $es_type, [
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
                                    '_source' => [
                                        'url',
                                        'title',
                                        'description',
                                        'created_at',
                                        'deleted_at',
                                        'sentiment'
                                    ]
                                ]);
                            break;
                            case 'video':
                                $q = [
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
                                    '_source' => [
                                        'title',
                                        'channel.id',
                                        'channel.title',
                                        'description',
                                        'created_at',
                                        'deleted_at',
                                        'sentiment'
                                    ]
                                ];

                                if ($request->string)
                                {
                                    $q['query']['bool']['should'][] = [
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
                                    ];
                                }

                                $documents = Document::search([ 'youtube', 'videos' ], $es_type, $q);
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
                                    '_source' => [
                                        'url',
                                        'title',
                                        'entry',
                                        'author',
                                        'created_at',
                                        'deleted_at',
                                        'sentiment'
                                    ]
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
                                        'created_at',
                                        'deleted_at',
                                        'sentiment'
                                    ]
                                ]);
                            break;
                        }
                    }
                break;
            }

            if (@$documents->data['hits']['hits'])
            {
                $total = number_format($documents->data['hits']['total']);

                $hits = array_map(function($arr) {
                    $array = $arr['_source'];

                    switch ($arr['_type'])
                    {
                        case 'tweet':
                            $array['text'] = Term::tweet($array['text']);
                        break;
                        case 'entry':
                            $array['text'] = $array['entry'];
                            unset($array['entry']);
                        break;
                        case 'article':
                        case 'document':
                        case 'product':
                            $array['text'] = $array['description'];
                            unset($array['description']);
                        break;
                    }

                    return array_merge(
                        $array,
                        [
                            '_id' => $arr['_id'],
                            '_type' => $arr['_type'],
                            '_index' => $arr['_index']
                        ]
                    );
                }, $documents->data['hits']['hits']);
            }
            else
            {
                $hits = [];
                $total = 0;
            }

            return [
                'status' => 'ok',
                'hits' => $hits,
                'total' => $total,
                'words' => @$clean->words ? $clean->words : []
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

    /**
     * Video Yorumları
     *
     * @return array
     */
    public static function videoComments(string $id, SearchRequest $request)
    {
        $arr = [
            'from' => $request->skip,
            'size' => $request->take,
            'sort' => [ 'created_at' => 'desc' ],
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'match' => strlen($id) == 11 ? [ 'video_id' => $id ] : [ 'channel.id' => $id ] ]
                    ]
                ]
            ],
            '_source' => [
                'text',
                'channel.id',
                'channel.title',
                'created_at'
            ]
        ];

        $clean = Term::cleanSearchQuery($request->string);

        if ($request->string)
        {
            $arr['query']['bool']['must'][] = [
                'query_string' => [
                    'query' => $request->string,
                    'default_operator' => 'AND'
                ]
            ];
        }

        $comments = Document::search([ 'youtube', 'comments', '*' ], 'comment', $arr);

        if ($comments->status == 'ok')
        {
            return [
                'status' => 'ok',
                'total' => $comments->data['hits']['total'],
                'hits' => array_map(function($arr) {
                    $replies = @Document::search([ 'youtube', 'comments', '*' ], 'comment', [
                        'sort' => [ 'created_at' => 'desc' ],
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'comment_id' => $arr['_id'] ] ],
                                ]
                            ]
                        ],
                        '_source' => [
                            'text',
                            'channel.id',
                            'channel.title',
                            'created_at'
                        ]
                    ])->data['hits']['hits'];

                    return [
                        'id' => $arr['_id'],
                        'text' => $arr['_source']['text'],
                        'channel' => $arr['_source']['channel'],
                        'created_at' => $arr['_source']['created_at'],

                        '_index' => $arr['_index'],
                        '_type' => $arr['_type'],
                        '_id' => $arr['_id'],

                        'replies' => array_map(function($sarr) {
                            return [
                                'text' => $sarr['_source']['text'],
                                'channel' => $sarr['_source']['channel'],
                                'created_at' => $sarr['_source']['created_at'],

                                '_index' => $sarr['_index'],
                                '_type' => $sarr['_type'],
                                '_id' => $sarr['_id'],
                            ];
                        }, $replies)
                    ];
                }, $comments->data['hits']['hits']),
                'words' => $clean->words
            ];
        }
        else
        {
            return [
                'status' => 'err',
                'reason' => 'Veritabanı bağlantısı kurulamadı.'
            ];
        }
    }

    /**
     * Kanal Videoları
     *
     * @return array
     */
    public static function channelVideos(string $id, SearchRequest $request)
    {
        $clean = Term::cleanSearchQuery($request->string);

        $data = [];

        $q = [
            'from' => $request->skip,
            'size' => $request->take,
            'sort' => [ 'created_at' => 'desc' ],
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'match' => [ 'channel.id' => $id ] ]
                    ]
                ]
            ],
            '_source' => [
                'created_at',
                'deleted_at',

                'title',
                'description',

                'channel.title',
                'channel.id',

                'sentiment'
            ]
        ];

        if ($request->string)
        {
            $q['query']['bool']['must'][] = [
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
            ];
        }

        $query = Document::search([ 'youtube', 'videos' ], 'video', $q);

        $hits = 0;

        if (@$query->data['hits']['hits'])
        {
            $hits = number_format($query->data['hits']['total']);

            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = [
                    'uuid' => md5($object['_id'].'.'.$object['_index']),
                    '_id' => $object['_id'],
                    '_type' => $object['_type'],
                    '_index' => $object['_index'],

                    'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at'])),

                    'sentiment' => $object['_source']['sentiment']
                ];

                if (@$object['_source']['deleted_at'])
                {
                    $arr['deleted_at'] = date('d.m.Y H:i:s', strtotime($object['_source']['deleted_at']));
                }

                $data[] = array_merge($arr, [
                    'title' => $object['_source']['title'],
                    'text' => @$object['_source']['description'],
                    'channel' => [
                        'id' => $object['_source']['channel']['id'],
                        'title' => $object['_source']['channel']['title']
                    ],
                ]);
            }
        }

        return [
            'status' => 'ok',
            'hits' => $data,
            'total' => $hits,
            'words' => $clean->words
        ];
    }
}
