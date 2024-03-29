<?php

namespace App\Console\Commands\Crawlers\Blog;

use Illuminate\Console\Command;

use App\Elasticsearch\Document;

use App\Jobs\Crawlers\Blog\TakerJob;

class Taker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:taker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tespit edilen BLOG kaynaklarını toplar.';

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
                'blog',
                '*'
            ],
            'document',
            [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [ 'match' => [ 'status' => 'buffer' ] ]
                        ]
                    ]
                ],
                '_source' => [
                    'id',
                    'url',
                    'source',
                    'site_id'
                ],
                'size' => 1000
            ]
        );

        if (@$query->data['hits']['hits'])
        {
            foreach ($query->data['hits']['hits'] as $array)
            {
                $this->info($array['_source']['url']);

                TakerJob::dispatch($array['_source'])->onQueue('power-crawler')->delay(now()->addSeconds(rand(1, 4)));
            }
        }
    }
}
