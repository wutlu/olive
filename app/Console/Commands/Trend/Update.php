<?php

namespace App\Console\Commands\Trend;

use Illuminate\Console\Command;
use App\Elasticsearch\Document;

use Carbon\Carbon;

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
                return $class->{$module}($period);
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

    /**
     * Trend Haber Tespiti
     *
     * @return array
     */
    public static function news(string $period)
    {
        switch ($period)
        {
            case 'minutely': $period = Carbon::now()->subMinutes(1)->format('Y-m-d H:i'); break;
            case 'hourly': $period = Carbon::now()->subMinutes(1)->format('Y-m-d H:i'); break;
            case 'daily': $period = Carbon::now()->subMinutes(1)->format('Y-m-d H:i'); break;
            case 'weekly': $period = Carbon::now()->subMinutes(1)->format('Y-m-d H:i'); break;
            case 'monthly': $period = Carbon::now()->subMinutes(1)->format('Y-m-d H:i'); break;
            case 'yearly': $period = Carbon::now()->subMinutes(1)->format('Y-m-d H:i'); break;
        }

        $items = [];

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
                                    'gte' => $period
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
                                [
                                    'match' => [ 'status' => 'ok' ]
                                ]
                            ],
                            'filter' => [
                                [
                                    'range' => [
                                        'created_at' => [
                                            'format' => 'YYYY-MM-dd HH:mm',
                                            'gte' => $period
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
                    }
                }
            }

            print_r($items);
        }
    }
}
