<?php

namespace App\Jobs\Crawlers\Media;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Crawlers\MediaCrawler;

use App\Utilities\Crawler;

use System;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

use Mail;
use App\Mail\ServerAlertMail;

class DetectorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $crawler;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MediaCrawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $links = Crawler::linkDetection($this->crawler->site, $this->crawler->url_pattern, $this->crawler->base);

        if (@$links->links)
        {
            $chunk = [ 'body' => [] ];

            foreach ($links->links as $link)
            {
                $chunk['body'][] = [
                    'create' => [
                        '_index' => Indices::name([ 'articles', $this->crawler->id ]),
                        '_type' => 'article',
                        '_id' => md5($link)
                    ]
                ];

                $chunk['body'][] = [
                    'id' => md5($link),
                    'bot_id' => $this->crawler->id,
                    'url' => $link,
                    'status' => 'buffer'
                ];
            }

            $bulk = Document::bulkInsert($chunk);

            if ($bulk->status == 'err')
            {
                $this->crawler->error_count = $this->crawler->error_count+1;

                $return = (object) [
                    'status' => 'err',
                    'message' => $bulk->message
                ];
            }
            else if ($bulk->status == 'ok')
            {
                $this->crawler->error_count = 0;

                $return = (object) [
                    'status' => 'ok'
                ];
            }
            else
            {
                $return = (object) [
                    'status' => 'err',
                    'message' => 'Bilinmeyen...'
                ];
            }
        }
        else
        {
            $return = (object) [
                'status' => 'err',
                'message' => $links->error_reasons
            ];
        }

        if ($return->status == 'err')
        {
            System::log(json_encode($return->message), 'App\Jobs\Crawlers\Media\DetectorJob::handle(int '.$this->crawler->id.')', 10);

            $this->crawler->error_count = $this->crawler->error_count+1;

            if ($this->crawler->error_count >= $this->crawler->off_limit)
            {
                if ($this->crawler->status == true)
                {
                    Mail::queue(new ServerAlertMail($this->crawler->name.' Medya Botu [DURDU]', json_encode($return->message)));
                }

                $this->crawler->off_reason = json_encode($return->message);
                $this->crawler->status = false;
                $this->crawler->test = false;
            }
        }

        $this->crawler->control_date = now();
        $this->crawler->save();
    }
}
