<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis as RedisCache;

use App\Elasticsearch\Document;

use Carbon\Carbon;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class Redis extends Command
{
    public $alias;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:store {--part=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Yoğun içerikleri redis\'e al.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->alias = config('system.db.alias');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $part = $this->option('part');

        if (!$part)
        {
            $part = $this->choice('Bir parça seçin:', [
                '__all',
                'total_document',
            ], 0);
        }

        switch ($part)
        {
            case '__all':
                $this->total_document();
                break;
            case 'total_document': $this->total_document(); break;
        }
    }

    /**
     * Toplam Döküman Sayısı
     *
     * @return mixed
     */
    public function total_document()
    {
        $client = new Client([
            'base_uri' => array_random(config('database.connections.elasticsearch.node.ips')),
            'handler' => HandlerStack::create()
        ]);

        $source = $client->get('/_cat/indices/'.$this->alias.'__*?format=json')->getBody();
        $source = json_decode($source);

        $data = array_map(function($arr) {
            return $arr->{'docs.count'};
        }, $source);

        RedisCache::set(implode(':', [ $this->alias, 'documents', 'total' ]), array_sum($data));

        $this->info(json_encode($data, JSON_PRETTY_PRINT));
    }
}
