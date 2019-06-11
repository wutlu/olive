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
                [ 'index' => 'youtube-videos',                   'type' => 'video' ],

                [ 'index' => 'youtube-comments-2019.02',         'type' => 'comment' ],
                [ 'index' => 'youtube-comments-2019.03',         'type' => 'comment' ],
                [ 'index' => 'youtube-comments-2019.04',         'type' => 'comment' ],
                [ 'index' => 'youtube-comments-2019.05',         'type' => 'comment' ],
                [ 'index' => 'youtube-comments-2019.06',         'type' => 'comment' ],
                [ 'index' => 'youtube-comments-2019.07',         'type' => 'comment' ],

                [ 'index' => 'sozluk-1',                         'type' => 'entry' ],
                [ 'index' => 'sozluk-2',                         'type' => 'entry' ],
                [ 'index' => 'sozluk-3',                         'type' => 'entry' ],
                [ 'index' => 'sozluk-4',                         'type' => 'entry' ],

                [ 'index' => 'twitter-tweets-2019.02',           'type' => 'tweet' ],
                [ 'index' => 'twitter-tweets-2019.03',           'type' => 'tweet' ],
                [ 'index' => 'twitter-tweets-2019.04',           'type' => 'tweet' ],
                [ 'index' => 'twitter-tweets-2019.05',           'type' => 'tweet' ],
                [ 'index' => 'twitter-tweets-2019.06',           'type' => 'tweet' ],
                [ 'index' => 'twitter-tweets-2019.07',           'type' => 'tweet' ],

                [ 'index' => 'media-s01',                        'type' => 'article' ],
                [ 'index' => 'media-s02',                        'type' => 'article' ],
                [ 'index' => 'media-s03',                        'type' => 'article' ],
                [ 'index' => 'media-s04',                        'type' => 'article' ],
                [ 'index' => 'media-s05',                        'type' => 'article' ],
                [ 'index' => 'media-s06',                        'type' => 'article' ],
                [ 'index' => 'media-s07',                        'type' => 'article' ],
                [ 'index' => 'media-s08',                        'type' => 'article' ],
                [ 'index' => 'media-s09',                        'type' => 'article' ],
                [ 'index' => 'media-s10',                        'type' => 'article' ],
                [ 'index' => 'media-s11',                        'type' => 'article' ],
                [ 'index' => 'media-s12',                        'type' => 'article' ],
                [ 'index' => 'media-s13',                        'type' => 'article' ],
                [ 'index' => 'media-s14',                        'type' => 'article' ],
                [ 'index' => 'media-s15',                        'type' => 'article' ],
                [ 'index' => 'media-s16',                        'type' => 'article' ],
                [ 'index' => 'media-s17',                        'type' => 'article' ],
                [ 'index' => 'media-s18',                        'type' => 'article' ],
                [ 'index' => 'media-s19',                        'type' => 'article' ],
                [ 'index' => 'media-s20',                        'type' => 'article' ],

                [ 'index' => 'shopping-1',                       'type' => 'product' ],
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
                                'consumer.nws' => [ 'type' => 'float' ],
                                'consumer.cmp' => [ 'type' => 'float' ],
                                'consumer.que' => [ 'type' => 'float' ],
                                'consumer.req' => [ 'type' => 'float' ],
                                'illegal.bet' => [ 'type' => 'float' ],
                                'illegal.nud' => [ 'type' => 'float' ]
                            ]
                        ]
                    ]
                ]
            );

            print_r($response);
        }
    }
}
