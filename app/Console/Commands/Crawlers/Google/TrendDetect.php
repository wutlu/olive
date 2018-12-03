<?php

namespace App\Console\Commands\Crawlers\Google;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Utilities\Term;

use App\Elasticsearch\Indices;

use App\Jobs\Elasticsearch\BulkInsertJob;

use App\Mail\ServerAlertMail;

use System;

use Mail;

use App\Wrawler;

use App\Models\Proxy;

class TrendDetect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:trend_detect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Google trend aramaları alır.';

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
            'base_uri' => 'https://trends.google.com',
            'handler' => HandlerStack::create()
        ]);

        // p24 parametresi Türkiye trendlerini temsil eder.

        $arr = [
            'timeout' => 10,
            'connect_timeout' => 5,
            'headers' => [
                'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))]
            ]
        ];

        $proxy = Proxy::where('health', '>', 5)->inRandomOrder()->first();

        if (@$proxy)
        {
            $arr['proxy'] = $proxy->proxy;
        }

        $source = $client->get('/trends/hottrends/atom/feed?pn=p24', $arr)->getBody();

        $saw = new Wrawler($source);
        $items = $saw->get('item')->toArray();

        $chunk = [];

        foreach ($items as $item)
        {
            try
            {
                $title = $item['title'][0]['#text'][0];
                $approx_traffic = preg_replace('/\D/', '', $item['approx_traffic'][0]['#text'][0]);
                $pubdate = date('Y-m-d H:i:s', strtotime($item['pubdate'][0]['#text'][0]));

                $id = md5($title.$pubdate);

                $this->info($title);

                $chunk['body'][] = [
                    'create' => [
                        '_index' => Indices::name([ 'google', 'search' ]),
                        '_type' => 'search',
                        '_id' => $id
                    ]
                ];

                $chunk['body'][] = [
                    'id' => $id,
                    'title' => $title,
                    'approx_traffic' => $approx_traffic,
                    'created_at' => date('Y-m-d H:i:s', strtotime($pubdate)),
                    'called_at' => date('Y-m-d H:i:s')
                ];
            }
            catch (\Exception $e)
            {
                $this->error($e->getMessage());

                System::log($e->getMessage(), 'App\Console\Commands\Crawlers\Google\TrendDetect::handle()', 2);
            }
        }

        if (count(@$chunk['body']))
        {
            BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
        }

        if (count(@$chunk['body']) <= 10)
        {
            Mail::queue(new ServerAlertMail('Google Arama [Düşük Verim]', 'Google arama toplama verimliliğinde yoğun bir düşüş yaşandı. Lütfen logları inceleyin.'));
        }
    }
}
