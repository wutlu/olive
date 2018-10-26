<?php

namespace App\Jobs\Crawlers\Shopping;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Crawlers\ShoppingCrawler;

use App\Utilities\Crawler;

use System;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

use Mail;
use App\Mail\ServerAlertMail;

use App\Jobs\Elasticsearch\BulkInsertJob;

class DetectorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $crawler;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ShoppingCrawler $crawler)
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
        $links = Crawler::googleSearchResultLinkDetection(
            $this->crawler->site,
            $this->crawler->url_pattern,
            $this->crawler->google_search_query,
            $this->crawler->google_max_page
        );

        if (@$links->links)
        {
            $chunk = [];

            foreach ($links->links as $link)
            {
                $chunk['body'][] = [
                    'create' => [
                        '_index' => Indices::name([ 'shopping', $this->crawler->id ]),
                        '_type' => 'product',
                        '_id' => md5($link)
                    ]
                ];

                $chunk['body'][] = [
                    'id' => md5($link),
                    'site_id' => $this->crawler->id,
                    'url' => $link,
                    'status' => 'buffer'
                ];
            }

            BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');

            $this->crawler->error_count = 0;

            $program = (object) [
                'status' => 'ok'
            ];
        }
        else
        {
            $program = (object) [
                'status' => 'err',
                'message' => $links->error_reasons
            ];
        }

        if ($program->status == 'err')
        {
            System::log(
                json_encode($program->message),
                'App\Jobs\Crawlers\Shopping\DetectorJob::handle(int '.$this->crawler->id.')',
                10
            );

            $this->crawler->error_count = $this->crawler->error_count + 1;

            if ($this->crawler->error_count >= $this->crawler->off_limit && $this->crawler->status == true)
            {
                Mail::queue(new ServerAlertMail($this->crawler->name.' Alışeriş Botu [DURDU]', json_encode($program->message)));

                $this->crawler->test       = false;
                $this->crawler->status     = false;
                $this->crawler->off_reason = json_encode($program->message);
            }
        }

        $this->crawler->control_date = now();
        $this->crawler->save();
    }
}
