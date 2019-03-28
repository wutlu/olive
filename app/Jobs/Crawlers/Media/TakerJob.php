<?php

namespace App\Jobs\Crawlers\Media;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Crawlers\MediaCrawler;

use App\Utilities\Crawler;
use App\Elasticsearch\Document;
use App\Elasticsearch\Indices;

use Mail;
use App\Mail\ServerAlertMail;

use System;
use Sentiment;

class TakerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $crawler = MediaCrawler::where('id', $this->data['site_id'])->first();

        if (@$crawler)
        {
            $item = Crawler::articleDetection(
                $crawler->site,
                $this->data['url'],
                $crawler->selector_title ? $crawler->selector_title : null,
                $crawler->selector_description ? $crawler->selector_description : null,
                $crawler->standard,
                $crawler->proxy
            );

            if ($item->status == 'ok')
            {
                $sentiment = new Sentiment;

                $upsert = Document::patch(
                    [
                        'media',
                        $crawler->elasticsearch_index_name
                    ],
                    'article',
                    $this->data['id'],
                    [
                        'script' => [
                            'source' => '
                                ctx._source.title = params.title;
                                ctx._source.description = params.description;
                                ctx._source.created_at = params.created_at;
                                ctx._source.called_at = params.called_at;
                                ctx._source.status = params.status;
                                ctx._source.sentiment = params.sentiment;
                            ',
                            'params' => [
                                'title' => $item->data['title'],
                                'description' => $item->data['description'],
                                'created_at' => $item->data['created_at'],
                                'called_at' => date('Y-m-d H:i:s'),
                                'status' => 'ok',
                                'sentiment' => $sentiment->score($item->data['description'])
                            ]
                        ]
                    ]
                );

                # Hata varken sorunsuz işlem gerçekleştirildiğinde hata alanını sıfırla.
                if ($crawler->error_count > 0)
                {
                    $crawler->update([ 'error_count' => 0 ]);
                }

                # ES hatalarını 10 dakika sonra tekrar dene.
                if ($upsert->status == 'err')
                {
                    TakerJob::dispatch($this->data)->onQueue('error-crawler')->delay(now()->addMinutes(10));
                }
            }
            else if ($item->status == 'err' || $item->status == 'failed')
            {
                $insert = Document::patch(
                    [
                        'media',
                        $crawler->elasticsearch_index_name
                    ],
                    'article',
                    $this->data['id'],
                    [
                        'doc' => [
                            'called_at' => date('Y-m-d H:i:s'),
                            'status' => 'failed',
                            'message' => $item->status == 'err' ? json_encode($item->error_reasons) : 'not_found'
                        ]
                    ]
                );

                $crawler->error_count = $crawler->error_count+1;

                if ($crawler->error_count >= $crawler->off_limit)
                {
                    System::log(
                        json_encode(
                            $item->error_reasons
                        ),
                        'App\Jobs\Crawlers\Media\TakerJob::handle(int '.$crawler->id.')',
                        10
                    );

                    if ($crawler->status == true)
                    {
                        Mail::queue(new ServerAlertMail($crawler->name.' Medya Botu [DURDU]', json_encode($item->error_reasons)));
                    }

                    $crawler->off_reason = json_encode($item->error_reasons);
                    $crawler->status = false;
                    $crawler->test = false;
                }

                $crawler->save();
            }
        }
        else
        {
            System::log(
                'Medya botu TakerJob tarafından bulunamadı.',
                'App\Jobs\Crawlers\Media\TakerJob::handle(int '.$this->data['site_id'].')',
                10
            );
        }
    }
}
