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
        $links = Crawler::articleLinkDetection(
            $this->crawler->site,
            $this->crawler->url_pattern,
            $this->crawler->base,
            $this->crawler->standard,
            $this->crawler->proxy,
            $this->crawler->cookie
        );

        if (@$links->links)
        {
            $chunk = [];

            foreach ($links->links as $link)
            {
                $chunk['body'][] = [
                    'create' => [
                        '_index' => Indices::name([ 'media', $this->crawler->elasticsearch_index_name ]),
                        '_type' => 'article',
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
                'App\Jobs\Crawlers\Media\DetectorJob::handle(int '.$this->crawler->id.')',
                10
            );

            $this->crawler->error_count = $this->crawler->error_count + 1;

            if ($this->crawler->error_count >= $this->crawler->off_limit && $this->crawler->status == true)
            {
                $recr = MediaCrawler::where('id', $this->crawler->id)->where('status', true)->exists();

                if ($recr)
                {
                    Mail::queue(new ServerAlertMail($this->crawler->name.' Medya Botu [DURDU]', json_encode($program->message)));

                    $this->crawler->test       = false;
                    $this->crawler->status     = false;
                    $this->crawler->off_reason = json_encode($program->message);
                }
            }
        }

        $this->crawler->control_date = now();
        $this->crawler->save();
    }
}
