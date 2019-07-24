<?php

namespace App\Jobs\Crawlers\Instagram;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Crawlers\Instagram\Selves;

use App\Instagram;

use App\Elasticsearch\Indices;
use App\Jobs\Elasticsearch\BulkInsertJob;

use System;
use Term;

use App\Utilities\DateUtility;

class SelfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $self;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Selves $self)
    {
        $this->self = $self;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $self = $this->self;

        $dateUtility = new DateUtility;

        $instagram = new Instagram;
        $connect = $instagram->connect($self->url);

        if ($connect->status == 'ok')
        {
            $data = $instagram->data($self->method);

            if ($data->status == 'ok')
            {
                $self->error_count = 0;
                $self->reason = null;
                $self->status = true;

                $bulk = [
                    'body' => []
                ];

                if ($self->method == 'user')
                {
                    $bulk['body'][] = [
                        'create' => [
                            '_index' => Indices::name([ 'instagram', 'users' ]),
                            '_type' => 'user',
                            '_id' => $data->user['id']
                        ]
                    ];
                    $bulk['body'][] = $data->user;
                }

                if (count($data->data))
                {
                    foreach ($data->data as $item)
                    {
                        if ($dateUtility->checkDate($item['created_at']))
                        {
                            $bulk['body'][] = [
                                'create' => [
                                    '_index' => Indices::name([ 'instagram', 'medias', date('Y.m', strtotime($item['created_at'])) ]),
                                    '_type' => 'media',
                                    '_id' => $item['id']
                                ]
                            ];
                            $bulk['body'][] = array_merge($item, [ 'self_id' => $self->id ]);
                        }
                    }
                }

                if (count($bulk['body']))
                {
                    BulkInsertJob::dispatch($bulk)->onQueue('elasticsearch');
                }
            }
            else
            {
                $self->error_count = $self->error_count+1;
                $_reason = $data->message;

                System::log(
                    $_reason,
                    'App\Jobs\Crawlers\Instagram\SelfJob::handle('.$self->id.')',
                    9
                );

                echo Term::line($_reason);
            }
        }
        else
        {
            $self->error_count = $self->error_count+1;

            System::log(
                $connect->message,
                'App\Jobs\Crawlers\Instagram\SelfJob::handle('.$self->url.')',
                8
            );

            echo Term::line($connect->code);
        }

        if ($self->error_count >= 40)
        {
            $self->status = false;
            $self->reason = @$_reason ? $_reason : 'Bir süredir bağlantıya ulaşılamıyor. Lütfen bunu silin ve tekrar oluşturun.';
        }

        $self->control_date = date('Y-m-d H:i:s');

        $self->save();
    }
}
