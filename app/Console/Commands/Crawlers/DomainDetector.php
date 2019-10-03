<?php

namespace App\Console\Commands\Crawlers;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Cookie\CookieJar;

use App\Wrawler;
use App\Models\TrendArchive;
use App\Models\Proxy;
use App\Models\DetectedDomains;

use App\Elasticsearch\Document;

use System;

class DomainDetector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domain:detector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Yeni haber sitesi, blog ve forum tespiti yapar.';

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
        $sentences = [];

        $modules = [ 'news' ];
        $trends = TrendArchive::whereIn('module', $modules)->where('group', '~*', '^\d{4}\.\d{1,2}\.\d{1,2}-\d{1,2}$')->take(count($modules))->orderBy('created_at', 'DESC')->get();

        if (count($trends))
        {
            foreach ($trends as $trend)
            {
                $document = Document::search([ 'trend', 'titles' ], 'title', [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [ 'match' => [ 'module' => $trend->module ] ],
                                [ 'match' => [ 'group' => date('Y.m.d-H', strtotime($trend->group)) ] ]
                            ]
                        ]
                    ],
                    'size' => 10
                ]);

                if ($document->status == 'ok')
                {
                    foreach ($document->data['hits']['hits'] as $hit)
                    {
                        $item = $hit['_source']['data'];

                        $sentences[] = $item['title'];
                    }
                }
                else
                {
                    $message = 'Elasticsearch bağlantısı kurulamadı.';

                    $this->error($message);

                    System::log( $message, 'App\Console\Commands\Crawlers\DomainDetector::handle('.$trend->module.')', 10 );
                }
            }

            print_r($sentences);

            $client = new Client([
                'base_uri' => 'https://www.google.com',
                'handler' => HandlerStack::create()
            ]);

            $domains = [];

            foreach ($sentences as $sentence)
            {
                foreach ([ 10 ] as $page)
                {
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

                        $dom = $client->get('https://www.google.com/search?q="'.$sentence.'"&tbs=qdr:d,sbd:1&start='.$page, $arr)->getBody();

                        $saw = new Wrawler($dom);

                        $cites = $saw->get('cite')->toArray();

                        if (count($cites))
                        {
                            foreach ($cites as $cite)
                            {
                                $domains[] = $this->domain($cite['#text'][0]);
                            }
                        }

                        $sleep = rand(1, 10);

                        $this->info('wait: '.$sleep);

                        sleep($sleep);
                    }
                    catch (\Exception $e)
                    {
                        $this->error($e->getMessage());

                        System::log( $e->getMessage(), 'App\Console\Commands\Crawlers\DomainDetector::handle('.$sentence.')', 10 );
                    }
                }
            }

            $domains = array_filter($domains);
            $domains = array_unique($domains);
            $domains = array_values($domains);

            print_r($domains);

            foreach ($domains as $domain)
            {
                $exists = DetectedDomains::where('domain', $domain)->exists();

                $this->line($domain);

                if (@$exists)
                {
                    $this->info('Var');
                }
                else
                {
                    $this->error('Yok');

                    $q = new DetectedDomains;
                    $q->domain = $domain;
                    $q->status = 'new';
                    $q->save();
                }
            }
        }
        else
        {
            $message = 'Trend arşivinde modül bulunamadı.';

            $this->error($message);

            System::log( $message, 'App\Console\Commands\Crawlers\DomainDetector::handle()', 10 );
        }
    }

    /**
     * Execute the console command.
     *
     * @return string
     */
    public static function domain(string $text)
    {
        $match = preg_match('/(http(s)?:\/\/)?(www\.)?([a-z0-9-\.]+)/', $text, $matches);
        $match = @$matches[0] ? $matches[0] : null;

        if ($match)
        {
            if (substr($match, 0, 4) == 'http')
            {
                $match = $match;
            }
            else if (substr($match, 0, 3) == 'www')
            {
                $match = 'http://'.$match;
            }
            else if (strpos($match, '.'))
            {
                $match = 'http://'.$match;
            }
            else
            {
                $match = false;
            }
        }
        else
        {
            return null;
        }

        return $match;
    }
}
