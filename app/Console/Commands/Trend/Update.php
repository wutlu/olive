<?php

namespace App\Console\Commands\Trend;

use Illuminate\Console\Command;

use App\Elasticsearch\Document;
use App\Elasticsearch\Indices;

use Carbon\Carbon;

use App\Models\Option;
use App\Models\Trend;

use App\Utilities\Term;

use App\Jobs\Elasticsearch\BulkInsertJob;

use Illuminate\Support\Facades\Redis as RedisCache;

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
                        'news' => 'List Index',
                    ]
                );
            }

            $class = new Update;

            if (method_exists($class, $module))
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
                            $date = Carbon::now()->subMinutes(10)->format('Y-m-d H:i');
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
                        'size' => 50,
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
                    }
                }
            }

            if ($i)
            {
                if ($period != 'live')
                {
                    Trend::updateOrCreate(
                        [
                            'group' => $group
                        ],
                        [
                            'title' => $group_title
                        ]
                    );

                    echo Term::line('Arşiv alındı.');
                }

                echo Term::line($group.' ('.$i.')');
            }

            if (count($chunk))
            {
                BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
            }

            if ($i && $period == 'live')
            {
                $array = [];

                foreach ($items as $rank => $item)
                {
                    $array[$rank]['title'] = $item['title'];

                    $ranks = array_reverse(array_map(
                        function($q) {
                            return $q['_source']['rank'];
                        },
                        Document::list([ 'trend', 'titles' ], 'title', [
                            'query' => [
                                'bool' => [
                                    'must' => [
                                        [ 'match' => [ 'key' => $item['key'] ] ]
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

                RedisCache::set(implode(':', [ $alias, 'trends', 'news' ]), json_encode($array));

                echo Term::line('Redis güncellendi.');
            }
        }
        else
        {
            echo Term::line('Trend tespit edilemedi.');
        }
    }
}
