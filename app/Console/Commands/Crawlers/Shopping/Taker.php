<?php

namespace App\Console\Commands\Crawlers\Shopping;

use Illuminate\Console\Command;

use App\Elasticsearch\Document;

use App\Jobs\Crawlers\Shopping\TakerJob;

class Taker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopping:taker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tespit edilen ürünleri topla.';

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
        $query = Document::search(
            [
                'shopping',
                '*'
            ],
            'product',
            [
                'query' => [
                    'bool' => [
                        'should' => [
                            [ 'match' => [ 'status' => 'buffer' ] ],
                            [ 'match' => [ 'status' => 'again' ] ],
                            [ 'match' => [ 'status' => 'last_time' ] ]
                        ]
                    ]
                ],
                '_source' => [
                    'id',
                    'url',
                    'source',
                    'site_id',
                    'status'
                ],
                'size' => 500
            ]
        );

        if (@$query->data['hits']['hits'])
        {
            foreach ($query->data['hits']['hits'] as $array)
            {
                $this->info($array['_source']['url']);

                TakerJob::dispatch($array['_source'])->onQueue('crawler')->delay(now()->addSeconds(rand(1, 40)));
            }
        }
    }
}
