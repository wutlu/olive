<?php

namespace App\Console\Commands\Crawlers;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Models\Proxy;
use App\Models\Currency;

use System;

class CurrencyUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Döviz değerlerini güncelle.';

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
        $client = new Client([
            'base_uri' => 'https://api.exchangeratesapi.io',
            'handler' => HandlerStack::create()
        ]);

        $data = [];

        $this->info('CALL: USD,EUR');

        try
        {
            $arr = [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                    'Accept-Language' => 'tr-TR;q=0.6,tr;q=0.4'
                ],
                'verify' => false,
                'allow_redirects' => [
                    'max' => 6
                ]
            ];

            $p = Proxy::where('ipv', 4)->where('health', '>', 7)->inRandomOrder()->first();

            if (@$p)
            {
                $arr['proxy'] = $p->proxy;
            }

            $dom = $client->get('latest?symbols=USD,EUR&base=TRY', $arr)->getBody();
            $array = json_decode($dom);

            foreach ($array->rates as $key => $value)
            {
                $data[] = [
                    'date' => date('Y-m-d H:i:s'),
                    'key' => $key,
                    'value' => 1/$value
                ];

                Currency::create(
                    [
                        'date' => date('Y-m-d H:i:s'),
                        'key' => $key,
                        'value' => 1/$value
                    ]
                );
            }
        }
        catch (\Exception $e)
        {
            System::log($e->getMessage(), 'App\Console\Commands\Crawlers\CurrencyUpdate::handle(usd,eur)', 10);

            $this->error($e->getMessage());
        }

        $this->info(json_encode($data, JSON_PRETTY_PRINT));
    }
}
