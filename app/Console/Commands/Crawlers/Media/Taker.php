<?php

namespace App\Console\Commands\Crawlers\Media;

use Illuminate\Console\Command;

use App\Elasticsearch\Document;

use Elasticsearch\ClientBuilder;

use App\Jobs\Crawlers\Media\TakerJob;

class Taker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:taker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Medya bağlantı toplayıcı.';

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
        $elasticsearch = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
            'retries' => 5
        ]);

        $query = Document::list(
            [
                'articles', '*'
            ],
            'article',
            [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'match' => [
                                    'status' => 'buffer'
                                ]
                            ]
                        ]
                    ]
                ],
                '_source' => [ 'id', 'url', 'source', 'bot_id' ],
                'size' => 500
            ]
        );

        if ($query->status == 'ok')
        {
            if (@$query->data['hits']['hits'])
            {
                foreach ($query->data['hits']['hits'] as $array)
                {
                    $obj = (object) $array;

                    $this->info($obj->_source['url']);

                    TakerJob::dispatch($obj->_source)->onQueue('crawler');
                }
            }
        }
    }
}
