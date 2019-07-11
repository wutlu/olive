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
        $min = 10;
        $size = 1000;

        $query = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
            'aggs' => [
                'results' => [
                    'terms' => [
                        'field' => 'external.id',
                        'size' => $size,
                        'min_doc_count' => $min
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
                                    'gte' => Carbon::now()->subDays(2)->format('Y-m-d H:i')
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
                $users = StreamingUsers::select('user_id')->where('organisation_id', config('app.organisation_id_root'))->pluck('user_id')->toArray();

                $search = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
                    'query' => [
                        'bool' => [
                            'should' => $ids,
                            'must' => [
                                [ 'match' => [ 'lang' => 'tr' ] ]
                            ],
                            'must_not' => [
                                [ 'match' => [ 'user.verified' => true ] ],
                                [
                                    'terms' => [
                                        'user.id' => $users
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'size' => $size,
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

                                $this->line($hit['_source']['user']['screen_name'].' - '.$hit['_id']);
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
