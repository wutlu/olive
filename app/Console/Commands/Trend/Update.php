<?php

namespace App\Console\Commands\Trend;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis as RedisCache;

use App\Elasticsearch\Document;
use App\Elasticsearch\Indices;
use App\Jobs\Elasticsearch\BulkInsertJob;

use Carbon\Carbon;

use App\Models\Option;
use App\Models\Trend;
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
    protected $signature = 'trend:update {--module=} {--period=}';

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
            'weekly' => 'Haftalık',
            'monthly' => 'Aylık',
            'yearly' => 'Yıllık',
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
                        case 'monthly':
                            $date = Carbon::now()->subMonths(1)->format('Y-m-d H:i');
                            $group = implode(':', [ $module, 'monthly', date('Y.m') ]);
                        break;
                        case 'yearly':
                            $date = Carbon::now()->subYears(1)->format('Y-m-d H:i');
                            $group = implode(':', [ $module, 'yearly', date('Y') ]);
                        break;
                    }

                    return $class->{$module}($period, $date, $group);
                }
                else
                {
                    $this->error('Geçersiz periyot!');
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
     * Trend Haber Tespiti
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
                $group_title = 'Medya: Anlık Trend, '.date('d.m.Y');
            break;
            case 'daily':
                $group_title = 'Medya: Günlük Trend, '.date('d.m.Y');
            break;
            case 'weekly':
                $group_title = 'Medya: Haftalık Trend, '.date('m.Y').' hafta: '.date('W');
            break;
            case 'monthly':
                $group_title = 'Medya: Aylık Trend, '.date('m.Y');
            break;
            case 'yearly':
                $group_title = 'Medya: Yıllık Trend, '.date('Y');
            break;
        }

        $query = @Document::list([ 'media', '*' ], 'article', [
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
                        'min_doc_count' => 2
                    ]
                ]
            ]
        ])->data['aggregations']['hit_keywords']['buckets'];

        if ($query)
        {
            $i = 0;

            foreach ($query as $row)
            {
                $title = @Document::list([ 'media', '*' ], 'article', [
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
                ])->data['hits']['hits'][0]['_source']['title'];

                if ($title)
                {
                    $sper = 0;

                    foreach ($items as $item)
                    {
                        similar_text($item['title'], $title, $percent);

                        $sper = $percent > $sper ? $percent : $sper;
                    }

                    if ($sper <= 50)
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
                    echo self::archive($group, $group_title);
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
     * Trend Tweet Tespiti
     *
     * @return mixed
     */
    public static function twitter(string $period, string $date, string $group)
    {
        $items = [];
        $chunk = [];
        $i = 0;

        try
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
        }
        catch (\Exception $e)
        {
            if ($e->getCode() == 401)
            {
                $level = 10;
            }
            else
            {
                $level = 6;
            }

            echo Term::line($e->getMessage());

            System::log($e->getMessage(), 'App\Console\Commands\Trend\Update::twitter('.$period.', '.$date.', '.$group.')', $level);
        }

        if ($i)
        {
            if ($period == 'live')
            {
                echo self::redis('twitter', $items, $date);
            }
        }

        if (count($chunk))
        {
            BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
        }
    }

    /**
     * Trend Google Arama Tespiti
     *
     * @return mixed
     */
    public static function google(string $period, string $date, string $group)
    {
        $items = [];
        $chunk = [];
        $i = 0;

        try
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
        }
        catch (\Exception $e)
        {
            echo Term::line($e->getMessage());

            System::log($e->getMessage(), 'App\Console\Commands\Trend\Update::google::handle('.$period.', '.$date.', '.$group.')', 2);
        }

        if ($i)
        {
            if ($period == 'live')
            {
                echo self::redis('google', $items, $date);
            }
        }

        if (count($chunk))
        {
            BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
        }
    }

    /**
     * Trend Google Arama Tespiti
     *
     * @return mixed
     */
    public static function youtube(string $period, string $date, string $group)
    {
        $items = [];
        $chunk = [];
        $i = 0;

        try
        {
            $trends = YouTube::getPopularVideos('tr', 50);

            foreach ($trends as $item)
            {
                $title = Term::convertAscii($item->snippet->title);

                if (Term::languageDetector([ $title ]))
                {
                    $i++;
                    $key = md5($title);
                    $id = 'youtube_'.$key.'_'.date('Y.m.d-H:i');

                    $items[$i] = [
                        'id' => $item->id,
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
                        'module' => 'youtube',
                        'rank' => $i,
                        'title' => $title,
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    ################

                    echo Term::line($i.' - '.$title);
                }
            }
        }
        catch (\Exception $e)
        {
            echo Term::line($e->getMessage());

            System::log($e->getMessage(), 'App\Console\Commands\Trend\Update::youtube::handle('.$period.', '.$date.', '.$group.')', 2);
        }

        if ($i)
        {
            if ($period == 'live')
            {
                echo self::redis('youtube', $items, $date);
            }
        }

        if (count($chunk))
        {
            BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
        }
    }

    /**
     * Gelen verinin trend arşivlerine (sql) alınması.
     *
     * @return string
     */
    private static function archive(string $group, string $group_title)
    {
        Trend::updateOrCreate(
            [ 'group' => $group ],
            [ 'title' => $group_title ]
        );

        return Term::line('Arşiv alındı.');
    }

    /**
     * Tespit edilen trendin Redis'e alınması.
     *
     * @return string
     */
    private static function redis(string $module, array $items, string $date)
    {
        $array = [];

        foreach ($items as $rank => $item)
        {
            $array[$rank]['id'] = $item['id'];
            $array[$rank]['title'] = $item['title'];

            $ranks = array_reverse(array_map(
                function($q) {
                    return $q['_source']['rank'];
                },
                Document::list([ 'trend', 'titles' ], 'title', [
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

            $array[$rank]['chart'] = $ranks;
        }

        $alias = str_slug(config('app.name'));

        RedisCache::set(implode(':', [ $alias, 'trends', $module ]), json_encode($array));

        return Term::line('Redis güncellendi.');
    }
}
