<?php

namespace App\Console\Commands\Trend;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis as RedisCache;

use App\Elasticsearch\Document;
use App\Elasticsearch\Indices;

use App\Jobs\Elasticsearch\BulkInsertJob;

use System;
use Sentiment;
use Term;

use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\BlogCrawler;
use App\Models\Crawlers\SozlukCrawler;
use App\Models\TrendArchive;
use App\Models\PopTrend;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Models\Proxy;
use App\Models\Twitter\BlockedTrendKeywords as TwitterBlockedTrendKeywords;
use App\Models\Instagram\BlockedTrendKeywords as InstagramBlockedTrendKeywords;

use App\Utilities\Crawler;

use App\Wrawler;

use Carbon\Carbon;

class Detect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trend:detect {--module=} {--time=} {--insert=} {--redis=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trend tespiti.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $alias = config('system.db.alias');

        $insert = $this->option('insert') ? true : false;
        $redis = $this->option('redis') ? true : false;

        $modules = [
            'twitter_tweet',
            'twitter_hashtag',
            'instagram_hashtag',
            'news',
            'blog',
            'entry',
            'youtube_video',
            'google',
            'google_live',
        ];

        $module = $this->option('module');

        $old_items = RedisCache::get(implode(':', [ $alias, 'trends', $module ]));

        if ($old_items)
        {
            $old_items = json_decode($old_items, true);
            $old_items = array_combine(array_column(array_column($old_items, 'data'), 'id'), $old_items);
        }

        if (!$module)
        {
            $module = $this->choice('Bir modül seçin', $modules, $module);
        }

        if ($module == 'google' || $module == 'google_live')
        {
            $time = '-10 minutes';
        }
        else
        {
            $times = [
                '-10 minutes',
                '-1 hours',
                '-1 days',
                '-7 days',
                '-1 months'
            ];

            $time = $this->option('time');

            if (!$time)
            {
                $time = $this->choice('Bir zaman belirtin', $times, $time);
            }
        }

        switch ($time)
        {
            case '-10 minutes':
                $group = 'live-'.str_random(16);
            break;
            case '-1 hours':
                $group = date('Y.m.d-H');
            break;
            case '-1 days':
                $group = date('Y.m.d');
            break;
            case '-7 days':
                $group = date('Y-W');
            break;
            case '-1 months':
                $group = date('Y.m');
            break;
        }

        switch ($module)
        {
            case 'twitter_tweet':
                $data = $this->twitterTweet($time);
            break;
            case 'twitter_hashtag':
                $data = $this->twitterHashtag($time);
            break;
            case 'instagram_hashtag':
                $data = $this->instagramHashtag($time);
            break;
            case 'news':
                $data = $this->news($time);
            break;
            case 'blog':
                $data = $this->blog($time);
            break;
            case 'entry':
                $data = $this->entry($time);
            break;
            case 'youtube_video':
                $data = $this->youtubeVideo($time);
            break;
            case 'google':
                $data = $this->google();
            break;
            case 'google_live':
                $data = $this->googleLive($alias);
            break;
        }

        $chunk = [];
        $results = [];

        if (count($data))
        {
            $rank = 1;

            foreach ($data as $item)
            {
                $arr = [
                    'group' => $group,
                    'module' => $module,
                    'hit' => $item['hit'],
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $pop_trend_id = null;

                switch ($module)
                {
                    case 'twitter_tweet':
                        $pop_trend_id = $item['user']['id'];
                        $pop_trend_details = [
                            'id' => $item['user']['id'],
                            'screen_name' => $item['user']['screen_name'],
                            'name' => $item['user']['name'],
                            'image' => $item['user']['image'],
                            'verified' => @$item['user']['verified'] ? true : false
                        ];

                        $arr['id'] = implode('-', [ $module, $group, $item['id'] ]);
                        $raw = [
                            'id' => $item['id'],
                            'text' => $item['text'],
                            'created_at' => $item['created_at'],
                            'user' => [
                                'id' => $item['user']['id'],
                                'screen_name' => $item['user']['screen_name'],
                                'name' => $item['user']['name'],
                                'image' => $item['user']['image']
                            ]
                        ];

                        if (@$item['user']['verified'])
                        {
                            $raw['user']['verified'] = true;
                        }

                        $arr['data'] = $raw;
                    break;
                    case 'twitter_hashtag':
                        $pop_trend_id = md5($item['key']);
                        $pop_trend_details = [
                            'title' => $item['key']
                        ];

                        $arr['id'] = implode('-', [ $module, $group, md5($item['key']) ]);
                        $arr['data'] = [
                            'id' => md5($item['key']),
                            'key' => $item['key']
                        ];
                    break;
                    case 'instagram_hashtag':
                        $pop_trend_id = md5($item['key']);
                        $pop_trend_details = [
                            'title' => $item['key']
                        ];

                        $arr['id'] = implode('-', [ $module, $group, md5($item['key']) ]);
                        $arr['data'] = [
                            'id' => md5($item['key']),
                            'key' => $item['key']
                        ];
                    break;
                    case 'news':
                        $arr['id'] = implode('-', [ $module, $group, md5($item['title']) ]);
                        $arr['data'] = [
                            'id' => md5($item['title']),
                            'title' => $item['title'],
                            'text' => $item['text']
                        ];

                        if (@$item['image'])
                        {
                            $arr['data']['image'] = $item['image'];
                        }
                    break;
                    case 'blog':
                        $arr['id'] = implode('-', [ $module, $group, md5($item['title']) ]);
                        $arr['data'] = [
                            'id' => md5($item['title']),
                            'title' => $item['title'],
                            'text' => $item['text']
                        ];

                        if (@$item['image'])
                        {
                            $arr['data']['image'] = $item['image'];
                        }
                    break;
                    case 'entry':
                        $pop_trend_id = md5($item['title']);
                        $pop_trend_details = [
                            'title' => $item['title']
                        ];

                        $arr['id'] = implode('-', [ $module, $group, md5($item['title']) ]);
                        $arr['data'] = [
                            'id' => md5($item['title']),
                            'title' => $item['title'],
                            'url' => $item['url']
                        ];
                    break;
                    case 'youtube_video':
                        $pop_trend_id = $item['channel']['id'];
                        $pop_trend_details = [
                            'id' => $item['channel']['id'],
                            'title' => $item['channel']['title']
                        ];

                        $arr['id'] = implode('-', [ $module, $group, $item['id'] ]);
                        $arr['data'] = [
                            'id' => $item['id'],
                            'title' => $item['title']
                        ];
                    break;
                    case 'google':
                        $pop_trend_id = md5($item['title']);
                        $pop_trend_details = [
                            'title' => $item['title']
                        ];

                        $arr['id'] = implode('-', [ $module, $group, md5($item['title']) ]);
                        $arr['data']['id'] = md5($item['title']);
                        $arr['data']['title'] = $item['title'];

                        if (@$item['image'])
                        {
                            $arr['data']['image'] = $item['image'];
                        }

                        if (@$item['text'])
                        {
                            $arr['data']['text'] = strip_tags($item['text']);
                        }
                    break;
                }

                if ($pop_trend_id)
                {
                    $pop_trend = PopTrend::where([ 'module' => $module, 'social_id' => $pop_trend_id ])->first();

                    if (@$pop_trend)
                    {
                        $length = Carbon::createFromFormat('Y-m-d H:i:s', $pop_trend->updated_at)->diffInHours(Carbon::now());

                        if ($length >= 6)
                        {
                            $pop_trend->trend_hit = $pop_trend->trend_hit+1;
                            $pop_trend->exp_trend_hit = $item['hit'] >= 50 ? ($pop_trend->exp_trend_hit+1) : $pop_trend->exp_trend_hit;
                        }
                    }
                    else
                    {
                        $pop_trend = new PopTrend;
                        $pop_trend->trend_hit = 1;
                        $pop_trend->exp_trend_hit = $item['hit'] >= 50 ? 1 : 0;

                        $pop_trend->module = $module;
                        $pop_trend->social_id = $pop_trend_id;
                    }

                    $pop_trend->details = $pop_trend_details;
                    $pop_trend->save();
                }

                $chunk['body'][] = [
                    'create' => [
                        '_index' => Indices::name([ 'trend', 'titles' ]),
                        '_type' => 'title',
                        '_id' => $arr['id']
                    ]
                ];
                $chunk['body'][] = $arr;

                $arr['rank'] = $rank;

                if ($old_items && @$old_items[$arr['data']['id']])
                {
                    $_rank = $old_items[$arr['data']['id']]['rank'];

                    $arr['ranks'] = array_merge([ $_rank ], [ $rank ]);
                }

                $results[] = $arr;

                $rank++;
            }
        }

        $this->info(json_encode($results, JSON_PRETTY_PRINT));

        if ($insert && count($chunk))
        {
            BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');

            $this->info('['.count($data).'] kayıt bulk insert edildi.');

            TrendArchive::firstOrCreate(
                [
                    'module' => $module,
                    'group' => $group
                ],
                [
                    'module' => $module,
                    'group' => $group
                ]
            );
        }

        if ($redis && count($results))
        {
            RedisCache::set(implode(':', [ $alias, 'trends', $module ]), json_encode($results));
            RedisCache::set(implode(':', [ $alias, 'trends', 'keys', $module ]), $group);

            $this->info('Redis güncellendi.');
        }
    }

    /**
     * Twitter Hashtags
     *
     * @return array
     */
    private function twitterTweet(string $time)
    {
        $query = Document::search([ 'twitter', 'tweets', date('Y.m') ], 'tweet', [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'called_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => date('Y-m-d H:i', strtotime($time))
                                ]
                            ]
                        ]
                    ],
                    'must' => [
                        [ 'match' => [ 'lang' => 'tr' ] ],
                        [
                            'range' => [ 'illegal.bet' => [ 'lte' => 0.3 ] ],
                            'range' => [ 'illegal.nud' => [ 'lte' => 0.3 ] ],
                        ]
                    ]
                ]
            ],
            'aggs' => [
                'top_hits' => [
                    'terms' => [
                        'field' => 'external.id',
                        'size' => 50,
                        'min_doc_count' => 10
                    ]
                ]
            ],
            'size' => 0
        ]);

        $data = [];

        if ($query->status == 'ok')
        {
            if (count($query->data['aggregations']['top_hits']['buckets']))
            {
                $ids = [];

                foreach ($query->data['aggregations']['top_hits']['buckets'] as $q)
                {
                    $ids[$q['key']] = $q['doc_count'];
                }

                $search = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'terms' => [
                                        'id' => array_keys($ids)
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '_source' => [
                        'id',
                        'text',
                        'created_at',
                        'user.id',
                        'user.screen_name',
                        'user.name',
                        'user.image',
                        'user.verified',
                    ],
                    'size' => 50
                ]);

                if ($search->status == 'ok')
                {
                    $hits = $search->data['hits']['hits'];
                    $hits = array_map(function($arr) use($ids) {
                        return array_merge(
                            $arr['_source'],
                            [
                                'hit' => $ids[$arr['_id']]
                            ]
                        );
                    }, $search->data['hits']['hits']);

                    usort($hits, '\App\Utilities\Term::hitSort');

                    $data = $hits;
                }
            }
        }

        return $data;
    }

    /**
     * Twitter Hashtags
     *
     * @return array
     */
    private function twitterHashtag(string $time)
    {
        $except = TwitterBlockedTrendKeywords::pluck('keyword')->toArray();

        $query = Document::search([ 'twitter', 'tweets', date('Y.m') ], 'tweet', [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'called_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => date('Y-m-d H:i', strtotime($time))
                                ]
                            ]
                        ]
                    ],
                    'must' => [
                        [
                            'nested' => [
                                'path' => 'entities.hashtags',
                                'query' => [
                                    'bool' => [ 'must' => [ [ 'exists' => [ 'field' => 'entities.hashtags.hashtag' ] ] ] ]
                                ]
                            ]
                        ],
                        [
                            'match' => [ 'lang' => 'tr' ]
                        ],
                        [
                            'range' => [ 'user.counts.favourites' => [ 'gte' => 40 ] ],
                            'range' => [ 'user.counts.statuses'   => [ 'gte' => 40 ] ],
                            'range' => [ 'user.counts.friends'    => [ 'gte' => 40 ] ],
                            'range' => [ 'user.counts.followers'  => [ 'gte' => 40 ] ],

                            'range' => [ 'illegal.bet' => [ 'lte' => 0.3 ] ],
                            'range' => [ 'illegal.nud' => [ 'lte' => 0.3 ] ],
                        ]
                    ],
                    /*
                    'must_not' => [
                        [ 'query_string' => [ 'query' => implode(' || ', $except) ] ]
                    ],
                    'must_not' => [
                        [
                            'nested' => [
                                'path' => 'entities.hashtags',
                                'query' => [
                                    'bool' => [
                                        'filter' => [
                                            [
                                                'terms' => [
                                                    'entities.hashtags.hashtag' => $except
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                    */
                ]
            ],
            'aggs' => [
                'hashtags' => [
                    'nested' => [ 'path' => 'entities.hashtags' ],
                    'aggs' => [
                        'hit_items' => [
                            'terms' => [
                                'field' => 'entities.hashtags.hashtag',
                                'size' => 50,
                                'min_doc_count' => 4,
                                'exclude' => $except
                            ]
                        ]
                    ]
                ]
            ],
            'size' => 0
        ]);

        $data = [];

        if ($query->status == 'ok')
        {
            if (count($query->data['aggregations']['hashtags']['hit_items']['buckets']))
            {
                foreach ($query->data['aggregations']['hashtags']['hit_items']['buckets'] as $bucket)
                {
                    if (mb_detect_encoding($bucket['key'], 'ASCII', true))
                    {
                        $key = str_slug($bucket['key']);

                        if (isset($data[$key]) && $data[$key]['hit'] > $bucket['doc_count'])
                        {
                            $data[$key]['hit'] = $bucket['doc_count'] + $data[$key]['hit'];
                        }
                        else
                        {
                            $data[$key] = [
                                'hit' => $bucket['doc_count'],
                                'key' => $bucket['key'],
                            ];
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Instagram Hashtags
     *
     * @return array
     */
    private function instagramHashtag(string $time)
    {
        $except = InstagramBlockedTrendKeywords::pluck('keyword')->toArray();

        $query = Document::search([ 'instagram', 'medias', date('Y.m') ], 'media', [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'called_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => date('Y-m-d H:i', strtotime($time))
                                ]
                            ]
                        ],
                        [
                            [ 'range' => [ 'counts.hashtag' => [ 'lte' => 5 ] ] ]
                        ]
                    ],
                    'must' => [
                        [
                            'nested' => [
                                'path' => 'entities.hashtags',
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            [ 'exists' => [ 'field' => 'entities.hashtags.hashtag' ] ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'range' => [ 'illegal.bet' => [ 'lte' => 0.3 ] ],
                            'range' => [ 'illegal.nud' => [ 'lte' => 0.3 ] ],
                        ]
                    ],
                ]
            ],
            'aggs' => [
                'hashtags' => [
                    'nested' => [ 'path' => 'entities.hashtags' ],
                    'aggs' => [
                        'hit_items' => [
                            'terms' => [
                                'field' => 'entities.hashtags.hashtag',
                                'size' => 50,
                                'min_doc_count' => 4,
                                'exclude' => $except
                            ]
                        ]
                    ]
                ]
            ],
            'size' => 0
        ]);

        $data = [];

        if ($query->status == 'ok')
        {
            if (count($query->data['aggregations']['hashtags']['hit_items']['buckets']))
            {
                foreach ($query->data['aggregations']['hashtags']['hit_items']['buckets'] as $bucket)
                {
                    if (mb_detect_encoding($bucket['key'], 'ASCII', true))
                    {
                        $key = str_slug($bucket['key']);

                        if (isset($data[$key]) && $data[$key]['hit'] > $bucket['doc_count'])
                        {
                            $data[$key]['hit'] = $bucket['doc_count'] + $data[$key]['hit'];
                        }
                        else
                        {
                            $data[$key] = [
                                'hit' => $bucket['doc_count'],
                                'key' => $bucket['key'],
                            ];
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * News, Article
     *
     * @return array
     */
    private function news(string $time)
    {
        $query = Document::search([ 'media', '*' ], 'article', [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'called_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => date('Y-m-d H:i', strtotime($time))
                                ]
                            ]
                        ]
                    ],
                    'must' => [
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ]
                ]
            ],
            'aggs' => [
                'title' => [
                    'terms' => [
                        'field' => 'title',
                        'size' => 50,
                        'min_doc_count' => 4,
                        'exclude' => [
                            'başarı',
                            'başarılı',
                            'belediye',
                            'belediyesi',
                            'başkan',
                            'başkanı',
                            'parti',
                            'video',
                            'görüntülü',
                            'görüntü',
                            'görüntül',
                            'haberi',
                            'haber',
                            'haberleri'
                        ]
                        /*
                        'script' => '(
                            (_value.length() >= 3) &&
                            (
                                _value != \'başarı\' &&
                                _value != \'başarılı\' &&
                                _value != \'belediye\' &&
                                _value != \'belediyesi\' &&
                                _value != \'başkan\' &&
                                _value != \'başkanı\' &&
                                _value != \'parti\' &&
                                _value != \'video\' &&
                                _value != \'görüntülü\' &&
                                _value != \'görüntü\' &&
                                _value != \'görüntül\' &&
                                _value != \'haberi\' &&
                                _value != \'haber\' &&
                                _value != \'haberleri\'
                            )
                        ) ? _value : \'_null_\''
                        */
                    ]
                ]
            ],
            'size' => 0
        ]);

        $data = [];

        if ($query->status == 'ok')
        {
            if (count($query->data['aggregations']['title']['buckets']))
            {
                $ids = MediaCrawler::where('alexa_rank', '<=', '10000')->pluck('id')->toArray();

                foreach ($query->data['aggregations']['title']['buckets'] as $bucket)
                {
                    if (mb_detect_encoding($bucket['key'], 'ASCII', true))
                    {
                        $key = str_slug($bucket['key']);

                        if (isset($data[$key]) && $data[$key]['hit'] > $bucket['doc_count'])
                        {
                            $data[$key]['hit'] = $bucket['doc_count'] + $data[$key]['hit'];
                        }
                        else
                        {
                            $search = Document::search([ 'media', '*' ], 'article', [
                                'query' => [
                                    'bool' => [
                                        'filter' => [
                                            [
                                                'range' => [
                                                    'called_at' => [
                                                        'format' => 'YYYY-MM-dd HH:mm',
                                                        'gte' => date('Y-m-d H:i', strtotime($time))
                                                    ]
                                                ]
                                            ],
                                            [
                                                'terms' => [
                                                    'site_id' => $ids
                                                ]
                                            ]
                                        ],
                                        'must' => [
                                            [
                                                'more_like_this' => [
                                                    'fields' => [ 'title' ],
                                                    'like' => $bucket['key'],
                                                    'min_term_freq' => 1,
                                                    'min_doc_freq' => 1,
                                                    'max_query_terms' => 10
                                                ]
                                            ],
                                            [ 'match' => [ 'status' => 'ok' ] ]
                                        ]
                                    ]
                                ],
                                '_source' => [
                                    'title',
                                    'description',
                                    'image_url',
                                ],
                                'size' => 4
                            ]);

                            if ($key != 'null')
                            {
                                $data[$key] = [
                                    'hit' => $bucket['doc_count'],
                                ];

                                if ($search->status == 'ok')
                                {
                                    if ($search->data['hits']['total'])
                                    {
                                        for ($i = 0; $i <= ($search->data['hits']['total']); $i++)
                                        {
                                            $source = @$search->data['hits']['hits'][$i]['_source'];

                                            if ($source)
                                            {
                                                $sper = 0;

                                                foreach ($data as $k => $item)
                                                {
                                                    if (@$item['title'])
                                                    {
                                                        similar_text($item['title'], $source['title'], $percent);

                                                        $sper = ($percent > $sper) ? $percent : $sper;
                                                    }
                                                }

                                                if ($sper <= 50)
                                                {
                                                    $data[$key]['title'] = $source['title'];
                                                    $data[$key]['text'] = $source['description'];

                                                    if (@$source['image_url'])
                                                    {
                                                        $data[$key]['image'] = $source['image_url'];
                                                    }

                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }

                                if (@$data[$key]['title'])
                                {
                                    //
                                }
                                else
                                {
                                    unset($data[$key]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Blog, Document
     *
     * @return array
     */
    private function blog(string $time)
    {
        $query = Document::search([ 'blog', '*' ], 'document', [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'called_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => date('Y-m-d H:i', strtotime($time))
                                ]
                            ]
                        ]
                    ],
                    'must' => [
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ]
                ]
            ],
            'aggs' => [
                'title' => [
                    'terms' => [
                        'field' => 'title',
                        'size' => 50,
                        'min_doc_count' => 1,
                        'exclude' => [
                            'başarı',
                            'başarılı',
                            'belediye',
                            'belediyesi',
                            'başkan',
                            'başkanı',
                            'parti',
                            'video',
                            'görüntülü',
                            'görüntü',
                            'görüntül',
                            'haberi',
                            'haber',
                            'haberleri'
                        ]
                        /*
                        'script' => '(
                            (_value.length() >= 3) &&
                            (
                                _value != \'başarı\' &&
                                _value != \'başarılı\' &&
                                _value != \'belediye\' &&
                                _value != \'belediyesi\' &&
                                _value != \'başkan\' &&
                                _value != \'başkanı\' &&
                                _value != \'parti\' &&
                                _value != \'video\' &&
                                _value != \'görüntülü\' &&
                                _value != \'görüntü\' &&
                                _value != \'görüntül\' &&
                                _value != \'haberi\' &&
                                _value != \'haber\' &&
                                _value != \'haberleri\'
                            )
                        ) ? _value : \'_null_\''
                        */
                    ]
                ]
            ],
            'size' => 0
        ]);

        $data = [];

        if ($query->status == 'ok')
        {
            if (count($query->data['aggregations']['title']['buckets']))
            {
                foreach ($query->data['aggregations']['title']['buckets'] as $bucket)
                {
                    if (mb_detect_encoding($bucket['key'], 'ASCII', true))
                    {
                        $key = str_slug($bucket['key']);

                        if (isset($data[$key]) && $data[$key]['hit'] > $bucket['doc_count'])
                        {
                            $data[$key]['hit'] = $bucket['doc_count'] + $data[$key]['hit'];
                        }
                        else
                        {
                            $search = Document::search([ 'blog', '*' ], 'document', [
                                'query' => [
                                    'bool' => [
                                        'filter' => [
                                            [
                                                'range' => [
                                                    'called_at' => [
                                                        'format' => 'YYYY-MM-dd HH:mm',
                                                        'gte' => date('Y-m-d H:i', strtotime($time))
                                                    ]
                                                ]
                                            ]
                                        ],
                                        'must' => [
                                            [
                                                'more_like_this' => [
                                                    'fields' => [ 'title' ],
                                                    'like' => $bucket['key'],
                                                    'min_term_freq' => 1,
                                                    'min_doc_freq' => 1,
                                                    'max_query_terms' => 10
                                                ]
                                            ],
                                            [ 'match' => [ 'status' => 'ok' ] ]
                                        ]
                                    ]
                                ],
                                '_source' => [
                                    'title',
                                    'description',
                                    'image_url',
                                ],
                                'size' => 4
                            ]);

                            if ($key != 'null')
                            {
                                $data[$key] = [
                                    'hit' => $bucket['doc_count'],
                                ];

                                if ($search->status == 'ok')
                                {
                                    if ($search->data['hits']['total'])
                                    {
                                        for ($i = 0; $i <= ($search->data['hits']['total']); $i++)
                                        {
                                            $source = @$search->data['hits']['hits'][$i]['_source'];

                                            if ($source)
                                            {
                                                $sper = 0;

                                                foreach ($data as $k => $item)
                                                {
                                                    if (@$item['title'])
                                                    {
                                                        similar_text($item['title'], $source['title'], $percent);

                                                        $sper = ($percent > $sper) ? $percent : $sper;
                                                    }
                                                }

                                                if ($sper <= 50)
                                                {
                                                    $data[$key]['title'] = $source['title'];
                                                    $data[$key]['text'] = $source['description'];

                                                    if (@$source['image_url'])
                                                    {
                                                        $data[$key]['image'] = $source['image_url'];
                                                    }

                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }

                                if (@$data[$key]['title'])
                                {
                                    //
                                }
                                else
                                {
                                    unset($data[$key]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Sözlük, Entry
     *
     * @return array
     */
    private function entry(string $time)
    {
        $query = Document::search([ 'sozluk', '*' ], 'entry', [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'called_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => date('Y-m-d H:i', strtotime($time))
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'aggs' => [
                'titles' => [
                    'terms' => [
                        'field' => 'group_name',
                        'size' => 50,
                        'min_doc_count' => 4
                    ],
                    'aggs' => [
                        'properties' => [
                            'top_hits' => [
                                'size' => 1,
                                '_source' => [
                                    'include' => [
                                        'title',
                                        'url'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'size' => 0
        ]);

        $data = [];

        if ($query->status == 'ok')
        {
            if (@$query->data['aggregations']['titles']['buckets'])
            {
                foreach ($query->data['aggregations']['titles']['buckets'] as $bucket)
                {
                    $data[] = [
                        'hit' => $bucket['doc_count'],
                        'title' => $bucket['properties']['hits']['hits'][0]['_source']['title'],
                        'url' => $bucket['properties']['hits']['hits'][0]['_source']['url']
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * YouTube, Video
     *
     * @return array
     */
    private function youtubeVideo(string $time)
    {
        $query = Document::search([ 'youtube', 'comments', date('Y.m') ], 'comment', [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'called_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => date('Y-m-d H:i', strtotime($time))
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'aggs' => [
                'videos' => [
                    'terms' => [
                        'field' => 'video_id',
                        'size' => 50,
                        'min_doc_count' => 4
                    ]
                ]
            ],
            'size' => 0
        ]);

        $data = [];

        if ($query->status == 'ok')
        {
            if (@$query->data['aggregations']['videos']['buckets'])
            {
                foreach ($query->data['aggregations']['videos']['buckets'] as $bucket)
                {
                    $video = Document::get([ 'youtube', 'videos' ], 'video', $bucket['key']);

                    if ($video->status == 'ok')
                    {
                        $arr = [
                            'hit' => $bucket['doc_count'],
                            'title' => $video->data['_source']['title'],
                            'id' => $bucket['key'],
                            'channel' => [
                                'id' => $video->data['_source']['channel']['id'],
                                'title' => $video->data['_source']['channel']['title']
                            ]
                        ];

                        $data[] = $arr;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Google, Trend Aramalar
     *
     * @return array
     */
    private function google()
    {
        $data = [];

        $each = true;

        while ($each == true)
        {
            try
            {
                $client = new Client([
                    'base_uri' => 'https://trends.google.com',
                    'handler' => HandlerStack::create()
                ]);

                $arr = [
                    'timeout' => 15,
                    'connect_timeout' => 15,
                ];

                $proxy = Proxy::where('health', '>', 7)->inRandomOrder()->first();

                if (@$proxy)
                {
                    $arr['proxy'] = $proxy->proxy;
                }

                $source = $client->get('/trends/hottrends/atom/feed?pn=p24', $arr)->getBody();

                $saw = new Wrawler($source);

                $array = $saw->get('item')->toArray();

                $rank = 1;

                foreach ($array as $item)
                {
                    $arr = [
                        'hit' => intval(str_replace([ ',', '+' ], '', $item['approx_traffic'][0]['#text'][0])),
                        'title' => $item['title'][0]['#text'][0]
                    ];

                    if (@$item['news_item'][0]['news_item_title'][0]['#text'][0])
                    {
                        $arr['text'] = $item['news_item'][0]['news_item_title'][0]['#text'][0];
                    }

                    if (@$item['picture'][0]['#text'][0])
                    {
                        $arr['image'] = $item['picture'][0]['#text'][0];
                    }

                    $data[] = $arr;

                    $rank++;
                }

                if ($rank > 1)
                {
                    $each = false;
                }
                else
                {
                    System::log('İçerik tespit edilemedi. 10 saniye sonra tekrar denenecek.', 'App\Console\Commands\Trend\Detect::google::handle()', 5);

                    sleep(10);
                }
            }
            catch (Exception $e)
            {
                System::log($e->getMessage(), 'App\Console\Commands\Trend\Detect::google::handle()', 2);
            }
        }

        return $data;
    }

    /**
     * Google, Live
     *
     * @return array
     */
    private function googleLive(string $alias)
    {
        $redis_data = json_decode(RedisCache::get(implode(':', [ $alias, 'trends', 'google' ])));
        $redis_data = array_map(function($arr) {
            $arr->data->count = Crawler::googleSearchCount($arr->data->title);

            return $arr;
        }, $redis_data);
    }
}
