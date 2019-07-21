<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Proxy as ProxyModel;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class Proxy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proxy yaşam değerlerini test et.';

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
        $proxies = ProxyModel::get();

        if (count($proxies))
        {
            foreach ($proxies as $proxy)
            {
                $this->info($proxy->proxy);

                $starttime = microtime(true);

                $client = new Client([
                    'base_uri' => config('app.url'),
                    'handler' => HandlerStack::create()
                ]);

                try
                {
                    $response = $client->get('/', [
                        'timeout' => 10,
                        'headers' => [
                            'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))]
                        ],
                        'proxy' => $proxy->proxy
                    ]);
                }
                catch (\Exception $e)
                {
                    $proxy->health = 0;
                }

                $endtime = microtime(true);

                $load_time = intval($endtime - $starttime);
                $load_time = 10 - ($load_time > 10 ? 10 : $load_time);

                $proxy->health = $load_time;

                $this->info('Health: ['.$proxy->health.']');

                $proxy->save();

                sleep(1);
            }
        }
    }
}
