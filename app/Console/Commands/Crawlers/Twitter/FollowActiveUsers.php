<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;

use App\Models\Twitter\StreamingUsers;

use App\Elasticsearch\Document;

use System;

use Carbon\Carbon;

class FollowActiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:follow_active_users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aktif Twitter kullanıcılarını takip eder.';

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
        $query = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
            'aggs' => [
                'results' => [
                    'terms' => [
                        'field' => 'external.id',
                        'size' => 10000,
                        'min_doc_count' => 50
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
                                    'gte' => Carbon::now()->subDay()->format('Y-m-d H:i')
                                ]
                            ]
                        ]
                    ],
                    'must' => [
                        [ 'match' => [ 'lang' => 'tr' ] ]
                    ]
                ]
            ],
            'size' => 0
        ]);

        if ($query->status == 'ok')
        {
            $ids = array_map(function ($item) {
                return [ 'match' => [ 'id' => $item['key'] ] ];
            }, $query->data['aggregations']['results']['buckets']);

            if (count($ids))
            {
                $search = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
                    'query' => [
                        'bool' => [
                            'should' => $ids,
                            'must_not' => [
                                [ 'match' => [ 'user.verified' => true ] ]
                            ]
                        ]
                    ],
                    'size' => 1000,
                    '_source' => [
                        'user.name',
                        'user.screen_name',
                        'user.id',
                    ]
                ]);

                if ($search->status == 'ok')
                {
                    if ($search->data['hits']['total'])
                    {
                        foreach ($search->data['hits']['hits'] as $hit)
                        {
                            try
                            {
                                StreamingUsers::updateOrCreate(
                                    [
                                        'user_id' => $hit['_source']['user']['id'],
                                        'organisation_id' => config('app.organisation_id_root')
                                    ],
                                    [
                                        'screen_name' => $hit['_source']['user']['screen_name']
                                    ]
                                );

                                $this->line($hit['_source']['user']['name']);
                            }
                            catch (\Exception $e)
                            {
                                System::log(
                                    $e->getMessage(),
                                    'App\Console\Commands\Crawlers\Twitter\FollowActiveUsers::handle(UPSERT)',
                                    10
                                );

                                $this->error($e->getMessage());
                            }
                        }
                    }
                    else
                    {
                        $message = '2. sorgudan tweet gelmedi.';
                    }
                }
                else
                {
                    $message = '2. sorgu çalışmadı.';
                }
            }
            else
            {
                $message = '1. sorgudan tweet tespit edilemedi.';
            }
        }
        else
        {
            $message = '1. sorgu çalışmadı.';
        }

        if (@$message)
        {
            $this->error($message);

            System::log(
                $message,
                'App\Console\Commands\Crawlers\Twitter\FollowActiveUsers::handle()',
                7
            );
        }
    }
}
