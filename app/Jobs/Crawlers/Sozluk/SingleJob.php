<?php

namespace App\Jobs\Crawlers\Sozluk;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Crawlers\SozlukCrawler;

use App\Utilities\Crawler as CrawlerUtility;

use App\Jobs\Elasticsearch\BulkInsertJob;

use App\Elasticsearch\Indices;

use Sentiment;
use Term;

class SingleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id;
    public $entry_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $id, int $entry_id)
    {
        $this->id = $id;
        $this->entry_id = $entry_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sozluk = SozlukCrawler::where('id', $this->id)->first();

        $entry_id = $this->entry_id;

        if (@$sozluk)
        {
            $item = CrawlerUtility::entryDetection(
                $sozluk->site,
                $sozluk->url_pattern,
                $entry_id,
                $sozluk->selector_title,
                $sozluk->selector_entry,
                $sozluk->selector_author,
                $sozluk->proxy
            );

            if ($item->status == 'ok')
            {
                $chunk['body'][] = [
                    'create' => [
                        '_index' => Indices::name([ 'sozluk', $sozluk->id ]),
                        '_type' => 'entry',
                        '_id' => $entry_id
                    ]
                ];

                $sentiment = new Sentiment;

                $chunk['body'][] = [
                    'id' => $entry_id,

                    'url' => $item->page,
                    'group_name' => $item->group_name,

                    'title' => $item->data['title'],
                    'entry' => $item->data['entry'],
                    'author' => $item->data['author'],

                    'created_at' => $item->data['created_at'],
                    'called_at' => date('Y-m-d H:i:s'),

                    'site_id' => $sozluk->id,

                    'sentiment' => $sentiment->score($item->data['entry'])
                ];

                BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
            }
            else
            {
                echo Term::line('failed');
            }
        }
        else
        {
            echo Term::line('Sözlük bulunamadı!');
        }
    }
}
