<?php

namespace App\Console\Commands\Trend;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis as RedisCache;

use App\Elasticsearch\Document;
use App\Elasticsearch\Indices;
use App\Jobs\Elasticsearch\BulkInsertJob;

use Carbon\Carbon;

use App\Models\Option;
use App\Models\TrendArchive;
use App\Models\Proxy;

use App\Utilities\Term;
use App\Mail\ServerAlertMail;
use App\Wrawler;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use YouTube;
use System;
use Mail;

class Update extends Command
{
    private $periods;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trend:update {--module=} {--period=} {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trend tespit eder.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->periods = [
            'live' => 'Anlık',
            'daily' => 'Günlük',
            'weekly' => 'Haftalık'
        ];

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $opt = Option::where('key', 'trend.index')->first();

        if (@$opt->value == 'on')
        {
            $module = $this->option('module');
            $period = $this->option('period');

            if (!$module)
            {
                $module = $this->choice(
                    'Modül seçin:',
                    [
                        'news' => 'Haber Trendleri',
                        'twitter' => 'Twitter Trendleri',
                        'google' => 'Google Trendleri',
                        'youtube' => 'YouTube Trendleri',
                        'sozluk' => 'Sözlük Trendleri',
                    ]
                );
            }

            $class = new Update;

            if (method_exists($class, $module))
            {
                $option = Option::where('key', implode('.', [ 'trend', 'status', $module ]))->where('value', 'on')->exists();

                if (!$option)
                {
                    $this->error('Trend: '.$module.' aktif değil.');

                    die();
                }

                if ($module == 'sozluk' || $module == 'news' || $module == 'youtube')
                {
                    if (!$period)
                    {
                        $period = $this->choice(
                            'Periyot seçin:',
                            $this->periods,
                            'live'
                        );
                    }

                    if (@$this->periods[$period])
                    {
                        switch ($period)
                        {
                            case 'live':
                                $date = Carbon::now()->subMinutes(30)->format('Y-m-d H:i');
                                $group = implode(':', [ $module, 'live', date('Y.m.d-H:i') ]);
                            break;
                            case 'daily':
                                $date = Carbon::now()->subDays(1)->format('Y-m-d H:i');
                                $group = implode(':', [ $module, 'daily', date('Y.m.d') ]);
                            break;
                            case 'weekly':
                                $date = Carbon::now()->subDays(7)->format('Y-m-d H:i');
                                $group = implode(':', [ $module, 'weekly', date('Y.m-W') ]);
                            break;
                        }

                        return $class->{$module}($period, $date, $group);
                    }
                    else
                    {
                        $this->error('Geçersiz periyot!');
                    }
                }
                else if ($module == 'twitter')
                {
                    $type = $this->option('type');

                    if (!$type)
                    {
                        $type = $this->choice(
                            'Tür seçin:',
                            [
                                'live' => 'Canlı',
                                'local' => 'Local'
                            ],
                            'live'
                        );
                    }

                    return $class->{$module}($type);
                }
                else
                {
                    return $class->{$module}();
                }
            }
            else
            {
                $this->error('Geçersiz modül!');
            }
        }
        else
        {
            $this->error('Önce index oluşturun.');
        }
    }

    /**
     * Trend Sözlük: Tespit
     *
     * @return mixed
     */
    public static function sozluk(string $period, string $date, string $group)
    {
        $items = [];
        $chunk = [];

        switch ($period)
        {
            case 'live':
                $group_title = 'Sözlük: Anlık Trend, '.date('Y.m.d H:i');
            break;
            case 'daily':
                $group_title = 'Sözlük: Günlük Trend, '.date('Y.m.d');
            break;
            case 'weekly':
                $group_title = 'Sözlük: Haftalık Trend, '.date('Y.m').' Hafta: '.date('W');
            break;
        }

        $query = Document::search([ 'sozluk', '*' ], 'entry', [
            'size' => 0,
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => $date
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'aggs' => [
                'hit_keywords' => [
                    'significant_terms' => [
                        'field' => 'title',
                        'size' => 25,
                        'min_doc_count' => 5
                    ]
                ]
            ]
        ]);

        $query = @$query->data['aggregations']['hit_keywords']['buckets'];

        if ($query)
        {
            $i = 0;

            foreach ($query as $row)
            {
                $document = Document::search([ 'sozluk', '*' ], 'entry', [
                    'size' => 1,
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'more_like_this' => [
                                        'fields' => [ 'title' ],
                                        'like' => $row['key'],
                                        'min_term_freq' => 1,
                                        'min_doc_freq' => 1,
                                        'max_query_terms' => 10
                                    ]
                                ]
                            ],
                            'filter' => [
                                [
                                    'range' => [
                                        'created_at' => [
                                            'format' => 'YYYY-MM-dd HH:mm',
                                            'gte' => $date
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '_source' => [ 'title', 'url' ]
                ]);

                $title = @$document->data['hits']['hits'][0]['_source']['title'];

                if ($title)
                {
                    $sper = 0;

                    foreach ($items as $item)
                    {
                        similar_text($item['title'], $title, $percent);

                        $sper = $percent > $sper ? $percent : $sper;
                    }

                    if ($sper <= 40)
                    {
                        $i++;
                        $key = md5($row['key']);
                        $id = 'sozluk_'.$key.'_'.date('Y.m.d-H:i');

                        $items[$i] = [
                            'key' => $key,
                            'title' => $title
                        ];

                        ################

                        $chunk['body'][] = [
                            'create' => [
                                '_index' => Indices::name([ 'trend', 'titles' ]),
                                '_type' => 'title',
                                '_id' => $id
                            ]
                        ];

                        $chunk['body'][] = [
                            'id' => $id,
                            'key' => $key,
                            'group' => $group,
                            'module' => 'sozluk',
                            'rank' => $i,
                            'title' => $title,
                            'created_at' => date('Y-m-d H:i:s')
                        ];

                        ################

                        echo Term::line($i.' - '.$title);
                    }
                }
            }

            if ($i)
            {
                if ($period == 'live')
                {
                    echo self::redis('sozluk', $items, $date);
                }
                else
                {
                    echo self::archive($group, $group_title, 'sozluk', $items);
                }
            }

            if (count($chunk))
            {
                BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
            }
        }
        else
        {
            echo Term::line('Trend tespit edilemedi.');
        }
    }

    /**
     * Trend Haber: Tespit
     *
     * @return mixed
     */
    public static function news(string $period, string $date, string $group)
    {
        $items = [];
        $chunk = [];

        switch ($period)
        {
            case 'live':
                $group_title = 'Haber: Anlık Trend, '.date('Y.m.d H:i');
            break;
            case 'daily':
                $group_title = 'Haber: Günlük Trend, '.date('Y.m.d');
            break;
            case 'weekly':
                $group_title = 'Haber: Haftalık Trend, '.date('Y.m').' Hafta: '.date('W');
            break;
        }

        $query = Document::search([ 'media', 's*' ], 'article', [
            'size' => 0,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            [ 'match' => [ 'status' => 'ok' ] ]
                        ]
                    ],
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => $date
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'aggs' => [
                'hit_keywords' => [
                    'significant_terms' => [
                        'field' => 'title',
                        'size' => 25,
                        'min_doc_count' => 5
                    ]
                ]
            ]
        ]);

        $query = @$query->data['aggregations']['hit_keywords']['buckets'];

        if ($query)
        {
            $i = 0;

            foreach ($query as $row)
            {
                $document = Document::search([ 'media', '*' ], 'article', [
                    'size' => 1,
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'more_like_this' => [
                                        'fields' => [ 'title' ],
                                        'like' => $row['key'],
                                        'min_term_freq' => 1,
                                        'min_doc_freq' => 1,
                                        'max_query_terms' => 10
                                    ]
                                ],
                                [ 'match' => [ 'status' => 'ok' ] ]
                            ],
                            'filter' => [
                                [
                                    'range' => [
                                        'created_at' => [
                                            'format' => 'YYYY-MM-dd HH:mm',
                                            'gte' => $date
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '_source' => [ 'title' ]
                ]);

                $title = @$document->data['hits']['hits'][0]['_source']['title'];

                if ($title)
                {
                    $sper = 0;

                    foreach ($items as $item)
                    {
                        similar_text($item['title'], $title, $percent);

                        $sper = $percent > $sper ? $percent : $sper;
                    }

                    if ($sper <= 40)
                    {
                        $i++;
                        $key = md5($row['key']);
                        $id = 'news_'.$key.'_'.date('Y.m.d-H:i');

                        $items[$i] = [
                            'key' => $key,
                            'title' => $title
                        ];

                        ################

                        $chunk['body'][] = [
                            'create' => [
                                '_index' => Indices::name([ 'trend', 'titles' ]),
                                '_type' => 'title',
                                '_id' => $id
                            ]
                        ];

                        $chunk['body'][] = [
                            'id' => $id,
                            'key' => $key,
                            'group' => $group,
                            'module' => 'news',
                            'rank' => $i,
                            'title' => $title,
                            'created_at' => date('Y-m-d H:i:s')
                        ];

                        ################

                        echo Term::line($i.' - '.$title);
                    }
                }
            }

            if ($i)
            {
                if ($period == 'live')
                {
                    echo self::redis('news', $items, $date);
                }
                else
                {
                    echo self::archive($group, $group_title, 'news', $items);
                }
            }

            if (count($chunk))
            {
                BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
            }
        }
        else
        {
            echo Term::line('Trend tespit edilemedi.');
        }
    }

    /**
     * Trend Google Arama: Tespit
     *
     * @return mixed
     */
    public static function youtube(string $period, string $date, string $group)
    {
        $items = [];
        $chunk = [];

        switch ($period)
        {
            case 'live':
                $group_title = 'YouTube: Anlık Trend, '.date('Y.m.d H:i');
            break;
            case 'daily':
                $group_title = 'YouTube: Günlük Trend, '.date('Y.m.d');
            break;
            case 'weekly':
                $group_title = 'YouTube: Haftalık Trend, '.date('Y.m').' Hafta: '.date('W');
            break;
        }

        $query = Document::search([ 'youtube', 'comments', '*' ], 'comment', [
            'size' => 0,
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => $date
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'aggs' => [
                'hit_keywords' => [
                    'significant_terms' => [
                        'field' => 'video_id',
                        'size' => 50,
                        'min_doc_count' => 1
                    ]
                ]
            ]
        ]);

        $query = @$query->data['aggregations']['hit_keywords']['buckets'];

        if ($query)
        {
            $i = 0;

            foreach ($query as $row)
            {
                $title = @Document::get([ 'youtube', 'videos' ], 'video', $row['key'])->data['_source']['title'];

                if ($title)
                {
                    $i++;
                    $key = md5($row['key']);
                    $id = 'youtube_'.$key.'_'.date('Y.m.d-H:i');

                    $items[$i] = [
                        'key' => $key,
                        'id' => $row['key'],
                        'title' => $title
                    ];

                    ################

                    $chunk['body'][] = [
                        'create' => [
                            '_index' => Indices::name([ 'trend', 'titles' ]),
                            '_type' => 'title',
                            '_id' => $id
                        ]
                    ];

                    $chunk['body'][] = [
                        'id' => $id,
                        'key' => $key,
                        'group' => $group,
                        'module' => 'youtube',
                        'rank' => $i,
                        'title' => $title,
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    ################

                    echo Term::line($i.' - '.$title);
                }
            }

            if ($i)
            {
                if ($period == 'live')
                {
                    echo self::redis('youtube', $items, $date);
                }
                else
                {
                    echo self::archive($group, $group_title, 'youtube', $items);
                }
            }

            if (count($chunk))
            {
                BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
            }
        }
        else
        {
            echo Term::line('Trend tespit edilemedi.');
        }
    }

    /**
     * Trend Tweet: Tespit
     *
     * @return mixed
     */
    public static function twitter(string $type)
    {
        $group = implode(':', [ 'twitter', 'live', date('Y.m.d-H') ]);

        $items = [];
        $chunk = [];
        $i = 0;

        switch ($type)
        {
            case 'live':
                try
                {
                    $each = true;

                    while ($each == true)
                    {
                        $stack = HandlerStack::create();

                        $oauth = new Oauth1([
                            'consumer_key' => config('services.twitter.client_id'),
                            'consumer_secret' => config('services.twitter.client_secret'),
                            'token' => config('services.twitter.access_token'),
                            'token_secret' => config('services.twitter.access_token_secret')
                        ]);

                        $stack->push($oauth);

                        $client = new Client(
                            [
                                'base_uri' => 'https://api.twitter.com/1.1/',
                                'handler' => $stack,
                                'auth' => 'oauth'
                            ]
                        );

                        $response = $client->get('trends/place.json', [
                            'query' => [
                                'id' => config('services.twitter.api.trend.id')
                            ],
                            'timeout' => 10,
                            'connect_timeout' => 5,
                            'headers' => [
                                'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                                'Accept' => 'application/json'
                            ]
                        ]);
                        $response = json_decode($response->getBody());

                        if (count(@$response[0]->trends))
                        {
                            foreach ($response[0]->trends as $trend)
                            {
                                $i++;
                                $key = md5($trend->name);
                                $id = 'twitter_'.$key.'_'.date('Y.m.d-H:i');

                                $items[$i] = [
                                    'key' => $key,
                                    'title' => $trend->name
                                ];

                                ################

                                $chunk['body'][] = [
                                    'create' => [
                                        '_index' => Indices::name([ 'trend', 'titles' ]),
                                        '_type' => 'title',
                                        '_id' => $id
                                    ]
                                ];

                                $chunk['body'][] = [
                                    'id' => $id,
                                    'key' => $key,
                                    'group' => $group,
                                    'module' => 'twitter',
                                    'rank' => $i,
                                    'title' => $trend->name,
                                    'created_at' => date('Y-m-d H:i:s')
                                ];

                                ################

                                echo Term::line($i.' - '.$trend->name);
                            }
                        }

                        if ($i > 0)
                        {
                            $each = false;
                        }
                        else
                        {
                            $message = 'İçerik tespit edilemedi. 10 saniye sonra tekrar denenecek.';

                            echo Term::line($message);

                            System::log($message, 'App\Console\Commands\Trend\Update::twitter::handle()', 5);

                            sleep(10);
                        }
                    }
                }
                catch (\Exception $e)
                {
                    echo Term::line($e->getMessage());

                    System::log($e->getMessage(), 'App\Console\Commands\Trend\Update::twitter()', $e->getCode() == 401 ? 10 : 6);
                }
            break;
            case 'local':
                $query = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
                    'aggs' => [
                        'tweets' => [
                            'nested' => [
                                'path' => 'entities.hashtags'
                            ],
                            'aggs' => [
                                'results' => [
                                    'terms' => [
                                        'field' => 'entities.hashtags.hashtag',
                                        'size' => 50,
                                        'min_doc_count' => 1
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'query' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'range' => [
                                        'created_at' => [
                                            'format' => 'YYYY-MM-dd HH:mm',
                                            'gte' => Carbon::now()->subHour()->format('Y-m-d H:i')
                                        ]
                                    ]
                                ]
                            ],
                            'must' => [
                                [ 'match' => [ 'lang' => 'tr' ] ]
                            ],
                            'must_not' => [
                                [ 'exists' => [ 'field' => 'external.id' ] ],
                                [
                                    'query_string' => [
                                        'fields' => [
                                            'text'
                                        ],
                                        'query' => implode(' OR ', config('services.twitter.blocked_words')),
                                        'default_operator' => 'AND'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'size' => 0
                ]);

                if ($query->status == 'ok')
                {
                    print_r($query->data['aggregations']['tweets']);
                    if ($query->data['aggregations']['tweets']['doc_count'])
                    {
                        foreach ($query->data['aggregations']['tweets']['results']['buckets'] as $item)
                        {
                            $i++;
                            $key = md5($item['key']);
                            $id = 'twitter_'.$key.'_'.date('Y.m.d-H:i');

                            $items[$i] = [
                                'key' => $key,
                                'title' => $item['key']
                            ];

                            ################

                            $chunk['body'][] = [
                                'create' => [
                                    '_index' => Indices::name([ 'trend', 'titles' ]),
                                    '_type' => 'title',
                                    '_id' => $id
                                ]
                            ];

                            $chunk['body'][] = [
                                'id' => $id,
                                'key' => $key,
                                'group' => $group,
                                'module' => 'twitter',
                                'rank' => $i,
                                'title' => $item['key'],
                                'created_at' => date('Y-m-d H:i:s')
                            ];

                            ################

                            echo Term::line($i.' - '.$item['key']);
                        }
                    }
                }
            break;
        }

        if ($i)
        {
            echo self::redis('twitter', $items, Carbon::now()->subHours(1)->format('Y-m-d H:i'));
        }

        if (count($chunk))
        {
            BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
        }
    }

    /**
     * Trend Google Arama: Tespit
     *
     * @return mixed
     */
    public static function google()
    {
        $group = implode(':', [ 'google', 'live', date('Y.m.d') ]);

        $items = [];
        $chunk = [];
        $i = 0;

        try
        {
            $each = true;

            while ($each == true)
            {
                $client = new Client([
                    'base_uri' => 'https://trends.google.com',
                    'handler' => HandlerStack::create()
                ]);

                $arr = [
                    'timeout' => 10,
                    'connect_timeout' => 5,
                    'headers' => [
                        'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))]
                    ]
                ];

                $proxy = Proxy::where('health', '>', 5)->inRandomOrder()->first();

                if (@$proxy)
                {
                    $arr['proxy'] = $proxy->proxy;
                }

                $source = $client->get(
                    '/trends/hottrends/atom/feed?pn=p24', // p24 parametresi Türkiye trendlerini temsil eder.
                    $arr
                )->getBody();

                $saw = new Wrawler($source);

                $array = $saw->get('item')->toArray();

                foreach ($array as $item)
                {
                    $title = $item['title'][0]['#text'][0];

                    $i++;
                    $key = md5($title);
                    $id = 'google_'.$key.'_'.date('Y.m.d-H:i');

                    $items[$i] = [
                        'key' => $key,
                        'title' => $title
                    ];

                    ################

                    $chunk['body'][] = [
                        'create' => [
                            '_index' => Indices::name([ 'trend', 'titles' ]),
                            '_type' => 'title',
                            '_id' => $id
                        ]
                    ];

                    $chunk['body'][] = [
                        'id' => $id,
                        'key' => $key,
                        'group' => $group,
                        'module' => 'google',
                        'rank' => $i,
                        'title' => $title,
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    ################

                    echo Term::line($i.' - '.$title);
                }

                if ($i > 0)
                {
                    $each = false;
                }
                else
                {
                    $message = 'İçerik tespit edilemedi. 10 saniye sonra tekrar denenecek.';

                    echo Term::line($message);

                    System::log($message, 'App\Console\Commands\Trend\Update::google::handle()', 5);

                    sleep(10);
                }
            }
        }
        catch (\Exception $e)
        {
            echo Term::line($e->getMessage());

            System::log($e->getMessage(), 'App\Console\Commands\Trend\Update::google::handle()', 2);
        }

        if ($i)
        {
            echo self::redis('google', $items, Carbon::now()->subDays(2)->format('Y-m-d H:i'));
        }

        if (count($chunk))
        {
            BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
        }
    }

    /**
     * Tespit edilen trendin Redis'e alınması.
     *
     * @return string
     */
    private static function redis(string $module, array $items, string $date)
    {
        $array = [];

        try
        {
            foreach ($items as $rank => $item)
            {
                if (@$item['id'])
                {
                    $array[$rank]['id'] = $item['id'];
                }

                $array[$rank]['title'] = $item['title'];

                $ranks = array_reverse(array_map(
                    function($q) {
                        return $q['_source']['rank'];
                    },
                    Document::search([ 'trend', 'titles' ], 'title', [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'key' => $item['key'] ] ],
                                    [ 'match' => [ 'module' => $module ] ]
                                ],
                                'filter' => [
                                    [
                                        'range' => [
                                            'created_at' => [
                                                'format' => 'YYYY-MM-dd HH:mm',
                                                'gte' => $date
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'sort' => [ 'created_at' => 'DESC' ]
                    ])->data['hits']['hits']
                ));

                $ranks[] = $rank;

                $array[$rank]['ranks'] = $ranks;
            }

            $alias = config('system.db.alias');

            RedisCache::set(implode(':', [ $alias, 'trends', $module ]), json_encode($array));

            return Term::line('Redis güncellendi.');
        }
        catch (\Exception $e)
        {
            echo Term::line('Elasticsearch hatası!');

            System::log($e->getMessage(), 'App\Console\Commands\Trend\Update::redis()', 10);
        }
    }

    /**
     * Gelen verinin trend arşivlerine (sql) alınması.
     *
     * @return string
     */
    private static function archive(string $group, string $group_title, string $module, array $items)
    {
        $alias = config('system.db.alias');

        if (count($items))
        {
            $name = config('system.trends')[implode('.', [ 'trend', 'status', $module ])];

            TrendArchive::updateOrCreate(
                [
                    'group' => $group
                ],
                [
                    'title' => $group_title,
                    'data' => $items
                ]
            );
        }

        return Term::line('Arşiv alındı.');
    }
}
