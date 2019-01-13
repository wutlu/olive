<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use App\Elasticsearch\Indices;

use App\Jobs\Elasticsearch\BulkInsertJob;

use App\Mail\ServerAlertMail;

use System;

use Mail;

class TrendDetect extends Command
{
    /**
     * Twitter Api Adresi
     *
     * @var string
     */
    private $endpoint = "https://api.twitter.com/1.1/";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:trend_detect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twitter trend başlıkları topla.';

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
        try
        {
            $stack = HandlerStack::create();

            $oauth = new Oauth1([
                'consumer_key' => config('services.twitter.client_id'),
                'consumer_secret' => config('services.twitter.client_secret'),
                'token' => config('services.twitter.access_token'),
                'token_secret' => config('services.twitter.access_token_secret')
            ]);

            $stack->push($oauth);

            $client = new Client(
                [
                    'base_uri' => $this->endpoint,
                    'handler' => $stack,
                    'auth' => 'oauth'
                ]
            );

            $response = $client->get('trends/place.json', [
                'query' => [
                    'id' => config('services.twitter.api.trend.id')
                ],
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                    'Accept' => 'application/json'
                ]
            ]);
            $response = json_decode($response->getBody());

            $chunk = [];

            if (count(@$response[0]->trends))
            {
                $i = 0;

                foreach ($response[0]->trends as $trend)
                {
                    $i++;

                    $id = date('YmdH').'_'.$i.'_'.md5($trend->name);

                    $chunk['body'][] = [
                        'create' => [
                            '_index' => Indices::name([ 'twitter', 'trends' ]),
                            '_type' => 'trend',
                            '_id' => $id
                        ]
                    ];

                    $arr = [
                        'title' => $trend->name,
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    if ($trend->tweet_volume)
                    {
                        $arr['approx_traffic'] = $trend->tweet_volume;
                    }

                    $chunk['body'][] = $arr;

                    $this->info($i.' - '.$trend->name);
                }
            }
        }
        catch (\Exception $e)
        {
            if ($e->getCode() == 401)
            {
                $level = 10;
            }
            else
            {
                $level = 6;
            }

            $this->error($e->getMessage());

            System::log($e->getMessage(), 'App\Console\Commands\Crawlers\Twitter\TrendDetect::handle()', $level);
        }

        if (count(@$chunk['body']))
        {
            BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');
        }

        if (count(@$chunk['body']) <= 10)
        {
            Mail::queue(new ServerAlertMail('Twitter, Trend Tespiti [Düşük Verim]', 'Twitter trend toplama verimliliğinde yoğun bir düşüş yaşandı. Lütfen logları inceleyin.'));
        }
    }
}
