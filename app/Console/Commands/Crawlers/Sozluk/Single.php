<?php

namespace App\Console\Commands\Crawlers\Sozluk;

use Illuminate\Console\Command;

use App\Models\Crawlers\SozlukCrawler;

use App\Utilities\Crawler as CrawlerUtility;

use App\Jobs\Elasticsearch\BulkInsertJob;

use App\Elasticsearch\Indices;

use Sentiment;

class Single extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sozluk:single {--id=} {--entry_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sözlük için tek entry alır.';

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
        $sozluk = SozlukCrawler::where('id', $this->option('id'))->first();

        $entry_id = $this->option('entry_id');

        if (@$sozluk)
        {
            $this->info($sozluk->name);

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
                $this->line($item->data['title']);

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

                $this->info('['.$entry_id.']');

                BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');

                if ($entry_id > $sozluk->last_id)
                {
                    $sozluk->last_id = $entry_id;
                    $sozluk->save();
                }
            }
            else
            {
                $this->error(json_encode($item->error_reasons, JSON_PRETTY_PRINT));
            }
        }
        else
        {
            $this->error('Sözlük bulunamadı!');
        }
    }
}
