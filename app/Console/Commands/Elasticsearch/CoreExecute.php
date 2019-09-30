<?php

namespace App\Console\Commands\Elasticsearch;

use Illuminate\Console\Command;

use Elasticsearch\ClientBuilder;

use App\Elasticsearch\Indices;

class CoreExecute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:core_execute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Yüklü Elasticsearch güncellemelerinin çalıştırılması.';

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
        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.node.ips'),
            'retries' => 5
        ]);

        foreach (
            [
                [ 'index' => 'twitter-tweets-2019.02',           'type' => 'tweet' ],
                [ 'index' => 'twitter-tweets-2019.03',           'type' => 'tweet' ],
                [ 'index' => 'twitter-tweets-2019.04',           'type' => 'tweet' ],
                [ 'index' => 'twitter-tweets-2019.05',           'type' => 'tweet' ],
                [ 'index' => 'twitter-tweets-2019.06',           'type' => 'tweet' ],
                [ 'index' => 'twitter-tweets-2019.07',           'type' => 'tweet' ],
            ] as $arr
        )
        {
            $response = $client->indices()->putMapping(
                [
                    'index' => config('system.db.alias').'__'.$arr['index'],
                    'type' => $arr['type'],
                    'body' => [
                        $arr['type'] => [
                            'properties' => [
                                'category' => [ 'type' => 'keyword' ],
                            ]
                        ]
                    ]
                ]
            );

            print_r($response);
        }
    }
}
