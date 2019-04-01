<?php

namespace App\Console\Commands\Crawlers\Media;

use Illuminate\Console\Command;

use App\Models\Crawlers\MediaCrawler;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Cookie\CookieJar;

use App\Wrawler;

use App\Models\Proxy;

use System;

use DB;

class Alexa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:alexa_ranker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Medya siteleri için alexa durumunu kontrol eder.';

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
        $crawlers = MediaCrawler::where('test', true)
                                ->where('error_count', '<', DB::raw("off_limit"))
                                ->get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                $this->line($crawler->site);

                $client = new Client([
                    'base_uri' => 'http://data.alexa.com',
                ]);

                $arr = [
                    'timeout' => 10,
                    'connect_timeout' => 5,
                    'headers' => [
                        'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                        'Accept' => 'application/xml'
                    ],
                    'verify' => false,
                    'query' => [
                        'cli' => 10,
                        'dat' => 's',
                        'url' => $crawler->site
                    ]
                ];

                try
                {
                    $p = Proxy::where('health', '>', 7)->inRandomOrder()->first();

                    if (@$p)
                    {
                        $arr['proxy'] = $p->proxy;
                    }

                    $xml = $client->get('data', $arr)->getBody();

                    $saw = new Wrawler($xml);
                    $meta_property = $saw->get('popularity')->toArray();

                    $rank = @$meta_property[0]['text'] ? $meta_property[0]['text'] : null;

                    if ($rank)
                    {
                        $this->info(number_format($rank));

                        $crawler->status = ($rank < config('app.alexa.rank_min')) ? true : false;
                        $crawler->alexa_rank = $rank;
                        $crawler->off_reason = $crawler->off_reason ? $crawler->off_reason : 'Alexa sıralaması çok düşük olduğundan görev sonlandırıldı.';
                        $crawler->update();
                    }
                    else
                    {
                        $this->error('Alexa verisi alınamadı.');
                    }
                }
                catch (\Exception $e)
                {
                    $this->error($e->getMessage());

                    System::log(
                        json_encode(
                            $e->getMessage()
                        ),
                        'App\Console\Commands\Crawlers\Media\Alexa::create('.$crawler->id.')',
                        10
                    );
                }
            }
        }
    }
}
