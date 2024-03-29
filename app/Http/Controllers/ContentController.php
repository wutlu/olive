<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Elasticsearch\Document;
use App\Elasticsearch\Indices;

use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\ShoppingCrawler;
use App\Models\Crawlers\BlogCrawler;
use App\Models\ReportedContents;

use Carbon\Carbon;

use System;

use App\Utilities\Term;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\IdRequest;
use App\Http\Requests\ClassificationRequest;

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
     * İçerik Bildirimi
     *
     * @return view
     */
    public static function reportedContents(int $pager = 10)
    {
        $data = ReportedContents::orderBy('id', 'DESC')->paginate($pager);

        if ($data->total() > $pager && count($data) == 0)
        {
            return redirect()->route('reported_contents');
        }

        return view('content.reported_contents', compact('data', 'pager'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * İçerik Bildirimi, Sil
     *
     * @return array
     */
    public static function deleteContentReport(IdRequest $request)
    {
        $query = ReportedContents::where('id', $request->id)->delete();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id
            ]
        ];
    }

    /**
     * İçerik Sınıflandır
     *
     * @return array
     */
    public static function classifier(ClassificationRequest $request)
    {
        $document = Document::get($request->index, $request->type, $request->id);

        if ($document->status == 'ok')
        {
            $arr = [];

            if ($request->sentiment)
            {
                $arr['sentiment']['pos'] = $request->sentiment == 'pos' ? 0.55 : 0.15;
                $arr['sentiment']['neu'] = $request->sentiment == 'neu' ? 0.55 : 0.15;
                $arr['sentiment']['neg'] = $request->sentiment == 'neg' ? 0.55 : 0.15;
                $arr['sentiment']['hte'] = $request->sentiment == 'hte' ? 0.55 : 0.15;
            }

            if ($request->consumer)
            {
                $arr['consumer']['que'] = $request->consumer == 'que' ? 0.55 : 0.15;
                $arr['consumer']['req'] = $request->consumer == 'req' ? 0.55 : 0.15;
                $arr['consumer']['nws'] = $request->consumer == 'nws' ? 0.55 : 0.15;
                $arr['consumer']['cmp'] = $request->consumer == 'cmp' ? 0.55 : 0.15;
            }

            if ($request->category)
            {
                $arr['category'] = config('system.analysis.category.types')[$request->category]['title'];
            }

            try
            {
                $doc = Document::patch($request->index, $request->type, $request->id, [
                    'doc' => $arr
                ]);

                ReportedContents::firstOrCreate(
                    [
                        '_id' => $request->id,
                        '_type' => $request->type,
                        '_index' => $request->index,
                    ],
                    [
                        'sentiment' => $request->sentiment ? $request->sentiment : null,
                        'consumer' => $request->consumer ? $request->consumer : null,
                        'category' => $request->category,
                        'user_id' => auth()->user()->id
                    ]
                );
            }
            catch (\Exception $e)
            {
                System::log('Elasticsearch bağlantısı sağlanamadı.',
                    'App\Http\Controllers\ContentController::classifier('.$request->index.', '.$request->type.', '.$request->id.')',
                    10
                );
            }

            return [
                'status' => 'ok'
            ];
        }
        else
        {
            return [
                'status' => 'err',
                'message' => 'Veritabanı bağlantısı sağlanamadı.'
            ];
        }
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

                    $data = [
                        'total' => Document::search($es_index, 'entry', [
                            'query' => [
                                'bool' => [
                                    'must' => [
                                        [ 'match' => [ 'author' => $document['_source']['author'] ] ]
                                    ]
                                ]
                            ],
                            'aggs' => [
                                'category' => [
                                    'terms' => [
                                        'field' => 'category',
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

                    $bucket = @$data['total']->data['aggregations']['category']['buckets'];

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

                        $data['category'] = $_temp_data;
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

                    if (@$document['_source']['price'] || @$document['_source']['seller'])
                    {
                        $data['dock'] = true;
                    }
                    else
                    {
                        $data['dock'] = false;
                    }

                    $title = implode(' ', [ $crawler->name, '/', '#'.$document['_source']['id'] ]);
                break;
                case 'tweet':
                    if (!$organisation->data_twitter)
                    {
                        return abort(403);
                    }

                    $title = implode(' / ', [ 'Twitter', $document['_source']['user']['name'], '#'.$es_id ]);

                    if (@$document['_source']['external']['id'])
                    {
                        $external = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [ 'query' => [ 'match' => [ 'id' => $document['_source']['external']['id'] ] ] ]);

                        $data['external'] = @$external->data['hits']['hits'][0];
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
                            'size' => 0
                        ])
                    ];

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
                                'category' => [
                                    'terms' => [
                                        'field' => 'category',
                                        'size' => 100
                                    ]
                                ]
                            ],
                            'size' => 0
                        ])
                    ];

                    $bucket = @$data['total']->data['aggregations']['category']['buckets'];

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

                        $data['category'] = $_temp_data;
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
                        $data['user'] = $user->data['_source'];
                    }
                    else
                    {
                        return view('content.media_loading', compact('document'));
                    }

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
     * Data
     *
     * @return array
     */
    public static function data(string $es_index, string $es_type, string $es_id)
    {
        $document = Document::get($es_index, $es_type, $es_id);

        if ($document->status == 'ok')
        {
            $data = [
                'status' => 'ok',
                'data' => $document->data['_source']
            ];

            switch ($es_type)
            {
                case 'comment':
                    $video = Document::get([ 'youtube', 'videos' ], 'video', $document->data['_source']['video_id']);

                    if ($video->status == 'ok')
                    {
                        $data['data']['video'] = $video->data['_source'];
                    }
                break;
                case 'tweet':
                    if (@$document->data['_source']['external']['id'])
                    {
                        $external = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
                            'query' => [
                                'bool' => [ 'must' => [ 'match' => [ 'id' => $document->data['_source']['external']['id'] ] ] ]
                            ]
                        ]);

                        if ($external->status == 'ok' && @$external->data['hits']['hits'][0])
                        {
                            $data['data']['original'] = $external->data['hits']['hits'][0]['_source'];
                        }
                    }
                break;
                case 'media':
                    if (@$document->data['_source']['user']['id'])
                    {
                        $external = Document::get([ 'instagram', 'users' ], 'user', $document->data['_source']['user']['id']);

                        if ($external->status == 'ok')
                        {
                            $data['data']['user'] = $external->data['_source'];
                        }
                    }
                break;
            }

            return $data;
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
            case 'category':
                $data['aggs']['category'] = [
                    'terms' => [
                        'field' => 'category'
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

        if ($user->status == 'ok')
        {
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
                case 'category':
                    $data['aggs']['category'] = [
                        'terms' => [
                            'field' => 'category'
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
        else
        {
            return [
                'status' => 'err',
                'message' => 'İçeriğe geçici olarak ulaşılamıyor.'
            ];
        }
    }

    /**
     * article Aggregations
     *
     * @return array
     */
    public static function articleAggregation(string $type, int $site_id)
    {
        $days = auth()->user()->organisation->historical_days;

        $crawler = MediaCrawler::where('id', $site_id)->firstOrFail();

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
                        [ 'match' => [ 'site_id' => $crawler->id ] ]
                    ]
                ]
            ],
            'size' => 0
        ];

        switch ($type)
        {
            case 'category':
                $data['aggs']['category'] = [
                    'terms' => [
                        'field' => 'category'
                    ]
                ];
            break;
        }

        $data = Document::search([ 'media', $crawler->elasticsearch_index_name ], 'article', $data);

        if ($data->status == 'ok')
        {
            switch ($type)
            {
                case 'category':
                    $data = $data->data['aggregations']['category']['buckets'];
                break;
            }

            return [
                'status' => 'ok',
                'data' => $data
            ];
        }
        else
        {
            return [
                'status' => 'err',
                'message' => 'İçeriğe geçici olarak ulaşılamıyor.'
            ];
        }
    }

    /**
     * blog Aggregations
     *
     * @return array
     */
    public static function documentAggregation(string $type, int $site_id)
    {
        $days = auth()->user()->organisation->historical_days;

        $crawler = BlogCrawler::where('id', $site_id)->firstOrFail();

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
                        [ 'match' => [ 'site_id' => $crawler->id ] ]
                    ]
                ]
            ],
            'size' => 0
        ];

        switch ($type)
        {
            case 'category':
                $data['aggs']['category'] = [
                    'terms' => [
                        'field' => 'category'
                    ]
                ];
            break;
        }

        $data = Document::search([ 'blog', $crawler->elasticsearch_index_name ], 'document', $data);

        if ($data->status == 'ok')
        {
            switch ($type)
            {
                case 'category':
                    $data = $data->data['aggregations']['category']['buckets'];
                break;
            }

            return [
                'status' => 'ok',
                'data' => $data
            ];
        }
        else
        {
            return [
                'status' => 'err',
                'message' => 'İçeriğe geçici olarak ulaşılamıyor.'
            ];
        }
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
            case 'category': $data['aggs']['category'] = [ 'terms' => [ 'field' => 'category' ] ]; break;
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
                        'match' => [ 'author' => $doc->data['_source']['author'] ]
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

                $smilar = Term::commonWords($doc->data['_source']['title']);

                $arr['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];
                $arr['query']['bool']['must'][] = [
                    'more_like_this' => [
                        'fields' => [ 'title', 'description' ],
                        'like' => array_keys($smilar),
                        'min_term_freq' => 1,
                        'min_doc_freq' => 1
                    ]
                ];

                $arr['min_score'] = 10;

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
                        'sort' => [
                            'created_at' => 'DESC'
                        ]
                    ];

                    switch ($type)
                    {
                        case 'tweet_replies':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'external.id' => $es_id ] ];
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'external.type' => 'reply' ] ];
                        break;
                        case 'tweet_quotes':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'external.id' => $es_id ] ];
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'external.type' => 'quote' ] ];
                        break;
                        case 'tweet_retweets':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'external.id' => $es_id ] ];
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'external.type' => 'retweet' ] ];
                        break;
                        case 'tweet_favorites':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'external.id' => $es_id ] ];
                            $arr['sort'] = [ 'counts.favorite' => 'desc' ];
                        break;
                        case 'tweet_deleted':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'external.id' => $es_id ] ];
                            $arr['query']['bool']['must'][] = [ 'exists' => [ 'field' => 'deleted_at' ] ];
                        break;
                        case 'user_tweets':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'user.id' => $document->data['_source']['user']['id'] ] ];
                            $arr['query']['bool']['must_not'][] = [ 'exists' => [ 'field' => 'external.id' ] ];
                        break;
                        case 'user_replies':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'user.id' => $document->data['_source']['user']['id'] ] ];
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'external.type' => 'reply' ] ];
                        break;
                        case 'user_quotes':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'user.id' => $document->data['_source']['user']['id'] ] ];
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'external.type' => 'quote' ] ];
                        break;
                        case 'user_retweets':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'user.id' => $document->data['_source']['user']['id'] ] ];
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'external.type' => 'retweet' ] ];
                        break;
                        case 'user_favorites':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'user.id' => $document->data['_source']['user']['id'] ] ];
                            $arr['sort'] = [ 'counts.favorite' => 'desc' ];
                        break;
                        case 'user_quotes_desc':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'user.id' => $document->data['_source']['user']['id'] ] ];
                            $arr['sort'] = [ 'counts.quote' => 'desc' ];
                        break;
                        case 'user_replies_desc':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'user.id' => $document->data['_source']['user']['id'] ] ];
                            $arr['sort'] = [ 'counts.reply' => 'desc' ];
                        break;
                        case 'user_retweets_desc':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'user.id' => $document->data['_source']['user']['id'] ] ];
                            $arr['sort'] = [ 'counts.retweet' => 'desc' ];
                        break;
                        case 'user_deleted':
                            $arr['query']['bool']['must'][] = [ 'match' => [ 'user.id' => $document->data['_source']['user']['id'] ] ];
                            $arr['query']['bool']['must'][] = [ 'exists' => [ 'field' => 'deleted_at' ] ];
                        break;
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
                                                    'match' => [ 'status' => 'ok' ]
                                                ],
                                                [
                                                    'more_like_this' => [
                                                        'fields' => [ 'title', 'description' ],
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
                                        'price',
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
