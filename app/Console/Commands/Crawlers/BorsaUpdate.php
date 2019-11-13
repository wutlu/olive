<?php

namespace App\Console\Commands\Crawlers;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Models\Proxy;
use App\Models\Borsa;
use App\Models\BorsaQuery;

use System;

use App\Wrawler;

class BorsaUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borsa:update {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Borsa değerlerini güncelle.';

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
        $type = $this->option('type');

        $types = [
            'xu030-bist-30' => 'xu030-bist-30',
            'xu100-bist-100' => 'xu100-bist-100',
        ];

        if (!$type)
        {
            $type = $this->choice('Bir borsa belirtin.', $types, $type);
        }

        $client = new Client([
            'base_uri' => 'http://www.mynet.com',
            'handler' => HandlerStack::create()
        ]);

        $this->info('CALL: '.$type);

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

            $dom = $client->get('amp/borsa/endeks/'.$type.'/endekshisseleri/', $arr)->getBody();

            $saw = new Wrawler($dom);

            $dom = $saw->get('table > tbody')->toDom();

            $lines = trim(preg_replace('/\t+/', '', $dom->textContent));

            $lines = preg_split('/\r\n|\r|\n/', $lines);

            $items = [];

            $line_count = 0;

            foreach ($lines as $line)
            {
                $line = trim($line);

                if (ctype_upper($line))
                {
                    $line_count++;
                }

                if ($line)
                {
                    $items[$line_count][] = $line;
                }
            }

            unset($items[0]);

            if (count($items))
            {
                $items = array_values(array_filter($items));

                $items = array_map(function($item) {
                    return [
                        'name' => $item[0],
                        'value' => $item[1],
                        'buy' => $item[2],
                        'sell' => $item[3],
                        'diff' => $item[4],
                        'min' => $item[5],
                        'max' => $item[6],
                        'hour' => $item[7],
                        'lot' => $item[8],
                        'tl' => $item[9]
                    ];
                }, $items);

                foreach ($items as $item)
                {
                    $insert = Borsa::where([
                        'name' => $item['name'],
                        'group' => $type,
                        'date' => date('Y-m-d')
                    ])->first();

                    BorsaQuery::firstOrCreate([
                        'name' => $item['name']
                    ]);

                    if (@$insert)
                    {
                        //
                    }
                    else
                    {
                        $insert = new Borsa;
                    }

                    $insert->name   = $item['name'];
                    $insert->group  = $type;
                    $insert->date   = date('Y-m-d');
                    $insert->hour   = $item['hour'];
                    $insert->value  = $item['value'];
                    $insert->buy    = $item['buy'];
                    $insert->sell   = $item['sell'];
                    $insert->diff   = $item['diff'];
                    $insert->min    = $item['min'];
                    $insert->max    = $item['max'];
                    $insert->lot    = str_replace( [ ',' ], [ '' ], $item['lot']);
                    $insert->tl     = str_replace( [ ',' ], [ '' ], $item['tl']);
                    $insert->save();
                }

                $this->info('Veriler güncellendi.');
            }
            else
            {
                $this->error('Borsa verisi gelmedi.');

                System::log('Borsa verisi gelmedi.', 'App\Console\Commands\Crawlers\BorsaUpdate::handle('.$type.')', 10);
            }
        }
        catch (Exception $e)
        {
            System::log($e->getMessage(), 'App\Console\Commands\Crawlers\BorsaUpdate::handle('.$type.')', 10);

            $this->error($e->getMessage());
        }
    }
}
