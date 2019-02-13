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
            'minutely' => 'Dakikalık',
            'hourly' => 'Saatlik',
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
                        'minutely'
                    );
                }

                if (@$this->periods[$period])
                {
                    switch ($period)
                    {
                        case 'minutely': $date = Carbon::now()->subMinutes(1)->format('Y-m-d H:i'); break;
                        case 'hourly': $date = Carbon::now()->subHours(1)->format('Y-m-d H:i'); break;
                        case 'daily': $date = Carbon::now()->subDays(1)->format('Y-m-d H:i'); break;
                        case 'weekly': $date = Carbon::now()->subDays(7)->format('Y-m-d H:i'); break;
                        case 'monthly': $date = Carbon::now()->subMonths(1)->format('Y-m-d H:i'); break;
                        case 'yearly': $date = Carbon::now()->subYears(1)->format('Y-m-d H:i'); break;
                    }

                    return $class->{$module}($period, $date);
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
     * @return array
     */
    public static function news(string $period, string $date)
    {
        $items = [];
        $chunk = [];

        switch ($period)
        {
            case 'minutely': $group = 'minutely_'.date('Y.'); break;
            case 'hourly': $group = 'hourly_'.date('Y.'); break;
            case 'daily':
                $group = 'daily_'.date('Y.m.d');
                $group_title = 'Günlük Trend, '.date('d.m.Y');
            break;
            case 'weekly':
                $group = 'weekly_'.date('Y.W');
                $group_title = 'Haftalık Trend, '.date('m.Y').' hafta: '.date('W');
            break;
            case 'monthly':
                $group = 'monthly_'.date('Y.m');
                $group_title = 'Aylık Trend, '.date('m.Y');
            break;
            case 'yearly':
                $group = 'yearly_'.date('Y');
                $group_title = 'Yıllık Trend, '.date('Y');
            break;
        }

        $query = @Document::list([ 'media', '*' ], 'article', [
            'size' => 0,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            [
                                'match' => [
                                    'status' => 'ok'
                                ]
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
                $i++;

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
                                [
                                    'match' => [ 'status' => 'ok' ]
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
                    '_source' => [ 'title' ]
                ])->data['hits']['hits'][0]['_source']['title'];

                if ($title)
                {
                    $sper = 0;

                    foreach ($items as $item)
                    {
                        similar_text($item, $title, $percent);

                        $sper = $percent > $sper ? $percent : $sper;
                    }

                    if ($sper <= 50)
                    {
                        $items[] = $title;

                        ################

                        $id = 'news_'.md5($row['key']).'_'.date('Y.m.d.H.i');

                        $chunk['body'][] = [
                            'create' => [
                                '_index' => Indices::name([ 'trend', 'titles' ]),
                                '_type' => 'title',
                                '_id' => $id
                            ]
                        ];

                        $chunk['body'][] = [
                            'id' => $id,
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
                if ($period != 'minutely' && $period != 'hourly')
                {
                    Trend::updateOrCreate(
                        [
                            'group' => $group
                        ],
                        [
                            'title' => $group_title
                        ]
                    );
                }

                echo Term::line($group_title.' ('.$i.')');
            }

            if (count($chunk))
            {
                BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
            }
        }
    }
}
