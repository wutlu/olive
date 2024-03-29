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
    protected $description = 'Tespit edilen medya kaynaklarını toplar.';

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
                'media',
                '*'
            ],
            'article',
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

                $upsert = Document::patch(
                    $array['_index'],
                    'article',
                    $array['_id'],
                    [
                        'script' => [
                            'source' => 'ctx._source.status = params.status;',
                            'params' => [
                                'status' => 'take'
                            ]
                        ]
                    ]
                );

                TakerJob::dispatch($array['_source'])->onQueue('power-crawler')->delay(now()->addSeconds(rand(1, 4)));
            }
        }
    }
}
