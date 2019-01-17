<?php

namespace App\Console\Commands\Crawlers\Media;

use Illuminate\Console\Command;

use App\Elasticsearch\Document;

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
    protected $description = 'Tespit edilen medya kaynaklarını topla.';

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
        $query = Document::list(
            [
                'articles',
                '*'
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
                '_source' => [ 'id', 'url', 'source', 'site_id' ],
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

                    TakerJob::dispatch($obj->_source)->onQueue('power-crawler')->delay(now()->addSeconds(rand(1, 4)));
                }
            }
        }
    }
}
