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
        $crawler = MediaCrawler::where('id', $this->data['bot_id'])->first();

        if (@$crawler)
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
            }
        }
        else
        {
            $insert = Indices::drop([ 'articles', $data->bot_id ]);
        }
    }
}
