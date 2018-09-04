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

use System;

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
        $crawler = MediaCrawler::where([
            'id' => $this->data['bot_id']
        ])->first();

        if (@$crawler)
        {
            if ($crawler->status)
            {
                $item = Crawler::articleDetection($crawler->site, $this->data['url'], $crawler->selector_title, $crawler->selector_description);

                if ($item->status == 'ok')
                {
                    $insert = Document::patch([ 'articles', $crawler->id ], 'article', $this->data['id'], [
                        'doc' => [
                            'title' => $item->data['title'],
                            'description' => $item->data['description'],
                            'created_at' => $item->data['created_at'],
                            'called_at' => date('Y-m-d H:i:s'),
                            'status' => 'ok'
                        ]
                    ]);

                    # Hata varken sorunsuz işlem gerçekleştirildiğinde hata alanını sıfırla.
                    if ($crawler->error_count > 0)
                    {
                        $crawler->update([ 'error_count' => 0 ]);
                    }

                    # ES hatalarını 10 dakika sonra tekrar dene.
                    if ($insert->status == 'err')
                    {
                        TakerJob::dispatch($this->data)->onQueue('crawler')->delay(now()->addMinutes(10));
                    }
                }
                else if ($item->status == 'err')
                {
                    $insert = Document::patch([ 'articles', $crawler->id ], 'article', $this->data['id'], [
                        'doc' => [
                            'called_at' => date('Y-m-d H:i:s'),
                            'status' => 'failed',
                            'message' => json_encode($item->error_reasons)
                        ]
                    ]);

                    $crawler->error_count = $crawler->error_count+1;

                    if ($crawler->error_count >= $crawler->off_limit)
                    {
                        System::log(json_encode($item->error_reasons), 'App\Jobs\Crawlers\Media\TakerJob::handle(int '.$crawler->id.')', 10);

                        if ($crawler->status == true)
                        {
                            Mail::queue(new ServerAlertMail($this->crawler->name.' Medya Botu [DURDU]', json_encode($item->error_reasons)));
                        }

                        $crawler->off_reason = json_encode($item->error_reasons);
                        $crawler->status = false;
                        $crawler->test = false;
                    }

                    $crawler->save();
                }
                else
                {
                    $insert = Indices::drop([ 'articles', $this->data['bot_id'] ]);
                }
            }
        }
    }
}
