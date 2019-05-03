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

use App\Olive\Gender;

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
            $gender = new Gender;
            $gender->loadNames();

            $item = CrawlerUtility::entryDetection(
                [
                    'site' => $sozluk->site,
                    'url_pattern' => $sozluk->url_pattern,
                    'selector_title' => $sozluk->selector_title,
                    'selector_entry' => $sozluk->selector_entry,
                    'selector_author' => $sozluk->selector_author,
                    'cookie' => $sozluk->cookie
                ],
                $entry_id,
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
                $sentiment->engine('sentiment');

                $chunk['body'][] = [
                    'id' => $entry_id,

                    'url' => $item->page,
                    'group_name' => $item->group_name,

                    'title' => $item->data['title'],
                    'entry' => $item->data['entry'],
                    'author' => $item->data['author'],
                    'gender' => $gender->detector([ $item->data['author'] ]),

                    'created_at' => $item->data['created_at'],
                    'called_at' => date('Y-m-d H:i:s'),

                    'site_id' => $sozluk->id,

                    'sentiment' => $sentiment->score($item->data['entry'])
                ];

                BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');

                if ($entry_id > $sozluk->last_id)
                {
                    $sozluk->last_id = $entry_id;
                    $sozluk->save();
                }

                echo Term::line($entry_id);
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
