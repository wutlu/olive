<?php

namespace App\Console\Commands\Newspaper;

use Illuminate\Console\Command;

use App\Wrawler;
use App\Utilities\Crawler;
use App\Utilities\DateUtility;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;

use App\Models\Proxy;

use Image;
use File;
use Term;

use thiagoalessio\TesseractOCR\TesseractOCR;

class Detector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newspaper:detector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gazete sayfalarını bulur ve indirir.';

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
        $site = (object) [
            'url' => 'https://www.gazeteoku.com',
            'pattern' => 'gazeteler\/([a-z0-9-]+)-manseti',
            'base' => 'gazeteler'
        ];

        $source = Crawler::articleLinkDetection(
            $site->url,
            $site->pattern,
            $site->base,
            false,
            true,
            false
        );

        if (@$source->links)
        {
            $this->info('[detected] '.count($source->links));

            foreach ($source->links as $link)
            {
                $this->info('[connect] '.$link);

                $data = [
                    'page' => $site->url,
                    'status' => 'ok'
                ];

                $client = new Client(
                    [
                        'base_uri' => $link,
                        'handler' => HandlerStack::create()
                    ]
                );

                try
                {
                    $arr = [
                        'timeout' => 5,
                        'connect_timeout' => 5,
                        'headers' => [
                            'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                            'Accept-Language' => 'tr-TR;q=0.6,tr;q=0.4'
                        ],
                        'curl' => [
                            CURLOPT_REFERER => $data['page'],
                            CURLOPT_COOKIE => 'AspxAutoDetectCookieSupport=1'
                        ],
                        'verify' => false
                    ];

                    $proxy = Proxy::where('ipv', 4)->where('health', '>', 7)->inRandomOrder()->first();

                    if (@$proxy)
                    {
                        $arr['proxy'] = $proxy->proxy;
                    }

                    $dom = $client->get($link, $arr)->getBody();

                    $saw = new Wrawler($dom);

                    $meta_property = $saw->get('meta[property]')->toArray();
                    $meta_name = $saw->get('meta[name]')->toArray();

                    $image = @array_first($meta_property, function ($value, $key) { return @$value['property'] == 'og:image'; })['content'];
                    $title = @array_first($meta_property, function ($value, $key) { return @$value['property'] == 'og:title'; })['content'];

                    if ($image)
                    {
                        if (!filter_var($image, FILTER_VALIDATE_URL))
                        {
                            $image = null;
                        }
                    }

                    $title = strlen($title) <= 4 ? null : $title;

                    $created_at = DateUtility::getDateInDom($dom);

                    $data = [
                        'status' => 'ok'
                    ];

                    # image
                    if ($image)
                    {
                        $data['data']['image_url'] = $image;
                    }
                    else
                    {
                        $data['status'] = 'err';
                        $data['error_reasons'][] = 'Resim tespit edilemedi.';
                    }

                    # title
                    if ($title)
                    {
                        $data['data']['title'] = $title;
                    }
                    else
                    {
                        $data['status'] = 'err';
                        $data['error_reasons'][] = 'Başlık tespit edilemedi.';
                    }

                    # date
                    if ($created_at)
                    {
                        $data['data']['created_at'] = $created_at;
                    }
                    else
                    {
                        $data['error_reasons'][] = 'Tarih tespit edilemedi.';
                    }
                }
                catch (\Exception $e)
                {
                    $data['status'] = 'failed';
                    $data['error_reasons'][] = $e->getMessage();
                }

                if ($data['status'] == 'ok')
                {
                    $folder = str_slug($data['data']['title']);
                    $path = storage_path('app/public/newspapers/'.$folder);

                    $filename = basename($data['data']['image_url']);

                    $file = $path.'/'.$filename;

                    if (!File::exists($path))
                    {
                        $this->info('[new folder] '.$folder);

                        File::makeDirectory($path, 0777, true, true);
                    }

                    $this->info('[download] '.$data['data']['image_url']);

                    Image::make($data['data']['image_url'])->save($file);

                    $this->info('[create ocr] '.$file);

                    $raw = (new TesseractOCR())
                        ->image($file)
                        ->lang('tur')
                        ->userWords(database_path('analysis/keywords.txt'))
                        ->run();

                    $raw = Term::convertAscii($raw);

                    $text = [];

                    foreach (preg_split('/\r\n|\r|\n/', $raw) as $line)
                    {
                        if (strlen($line) >= 3)
                        {
                            $text[] = $line;
                        }
                    }

                    if (count($text) >= 10)
                    {
                        $this->info('[elasticsearch]');
                        $this->info('[ok]');
                    }
                    else
                    {
                        $this->error('[failed] ocr lines <= 10');
                    }
                }
                else
                {
                    foreach ($data['error_reasons'] as $reason)
                    {
                        $this->error('['.$data['status'].'] '.$reason);
                    }
                }
            }
        }
    }
}
