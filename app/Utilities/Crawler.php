<?php

namespace App\Utilities;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Wrawler;
use App\Utilities\DateUtility;
use App\Utilities\Term;

use Carbon\Carbon;

use App\Models\Proxy;
use App\Models\Crawlers\Host;

class Crawler
{
    /**
     * Medya, Bağlantı Tespiti
     *
     * @return array
     */
    public static function mediaLinkDetection(string $site, string $url_pattern, string $base = '/', bool $proxy = false)
    {
        $data = [];

        $client = new Client([
            'base_uri' => $site,
            'handler' => HandlerStack::create()
        ]);

        try
        {
            $arr = [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))]
                ],
                'curl' => [
                    CURLOPT_REFERER => $site
                ],
                'verify' => false
            ];

            if ($proxy)
            {
                $p = Proxy::where('health', '>', 7)->inRandomOrder()->first();

                if (@$p)
                {
                    $arr['proxy'] = $p->proxy;
                }
            }

            $dom = $client->get($base, $arr)->getBody();

            $ip = gethostbyname(str_replace([ 'https://', 'http://', 'www.' ], '', $site));

            Host::firstOrCreate(
                [
                    'site' => $site,
                    'ip_address' => $ip
                ]
            );

            preg_match_all('/'.$url_pattern.'/', $dom, $match);

            if (@$match[0])
            {
                $match = array_unique($match[0]); 

                foreach ($match as $item)
                {
                    $data['links'][] = $site.'/'.str_after($item, $site.'/');
                }
            }
            else
            {
                $data['error_reasons'][] = 'Girilen desen hiçbir sonuç ile eşleşmedi.';
            }
        }
        catch (\Exception $e)
        {
            $data['error_reasons'][] = $e->getMessage();
        }

        return (object) $data;
    }

    /**
     * Google Arama Sonucu, Bağlantı Tespiti
     *
     * @return array
     */
    public static function googleSearchResultLinkDetection(string $site, string $url_pattern, string $query, string $google_time, int $max_page = 1)
    {
        $data = [];

        $client = new Client([
            'base_uri' => 'https://www.google.com',
            'handler' => HandlerStack::create()
        ]);

        for ($i = 0; $i <= $max_page-1; $i++)
        {
            $page = $i*10;

            try
            {
                $arr = [
                    'timeout' => 10,
                    'connect_timeout' => 5,
                    'headers' => [
                        'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))]
                    ],
                    'curl' => [
                        CURLOPT_REFERER => $site
                    ],
                    'verify' => false
                ];

                $proxy = Proxy::where('health', '>', 7)->inRandomOrder()->first();

                if (@$proxy)
                {
                    $arr['proxy'] = $proxy->proxy;
                }

                $dom = $client->get('https://www.google.com/search?q='.$query.'&tbs=qdr:'.$google_time.',sbd:1&start='.$page, $arr)->getBody();

                $ip = gethostbyname(str_replace([ 'https://', 'http://', 'www.' ], '', $site));

                Host::firstOrCreate(
                    [
                        'site' => $site,
                        'ip_address' => $ip
                    ]
                );

                preg_match_all('/'.$url_pattern.'/', $dom, $match);

                if (@$match[0])
                {
                    $match = array_unique($match[0]); 

                    foreach ($match as $item)
                    {
                        $data['links'][] = $site.'/'.str_after($item, $site.'/');
                    }
                }
                else
                {
                    $data['error_reasons'][] = 'Girilen desen hiçbir sonuç ile eşleşmedi.';
                }
            }
            catch (\Exception $e)
            {
                $data['error_reasons'][] = $e->getMessage();
            }
        }

        return (object) $data;
    }

    /**
     * Makale Tespiti
     *
     * @return array
     */
    public static function articleDetection(string $site, string $page, string $title_selector, string $description_selector, bool $proxy = false)
    {
        $data = [
            'page' => $page,
            'status' => 'ok'
        ];

        $client = new Client([
            'base_uri' => $site,
            'handler' => HandlerStack::create()
        ]);

        try
        {
            $arr = [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))]
                ],
                'curl' => [
                    CURLOPT_REFERER => $data['page']
                ],
                'verify' => false
            ];

            if ($proxy)
            {
                $p = Proxy::where('health', '>', 7)->inRandomOrder()->first();

                if (@$p)
                {
                    $arr['proxy'] = $p->proxy;
                }
            }

            $dom = $client->get($page, $arr)->getBody();

            $dom = str_replace('&nbsp;', ' ', $dom);

            $saw = new Wrawler($dom);

            $meta_property = $saw->get('meta[property]')->toArray();
            $meta_name = $saw->get('meta[name]')->toArray();

            # title detect
            $title = $saw->get($title_selector)->toText();

            if (!$title)
            {
                $data['error_reasons'][] = 'Başlık tespit edilemedi. Alternatif denendi.';

                $title = @array_first($meta_property, function ($value, $key) { return @$value['property'] == 'og:title'; })['content'];

                if (!$title)
                {
                    $title = @array_first($meta_name, function ($value, $key) { return @$value['name'] == 'twitter:title'; })['content'];
                }
            }

            $title = Term::convertAscii($title);

            # description detect
            $description = $saw->get($description_selector)->toText();

            if (!$description)
            {
                $data['error_reasons'][] = 'Açıklama tespit edilemedi. Alternatif denendi.';

                $description = @array_first($meta_property, function ($value, $key) { return @$value['property'] == 'og:description'; })['content'];

                if (!$description)
                {
                    $description = @array_first($meta_name, function ($value, $key) { return @$value['name'] == 'twitter:description'; })['content'];
                }

                if (!$description)
                {
                    $description = @array_first($meta_name, function ($value, $key) { return @$value['name'] == 'description'; })['content'];
                }
            }

            $description = Term::convertAscii($description);

            $created_at = DateUtility::getDateInDom($dom);

            $data['data'] = [
                'title' => $title,
                'description' => $description,
                'created_at' => $created_at
            ];

            # date
            if (!$created_at)
            {
                $data['error_reasons'][] = 'Tarih tespit edilemedi.';
                $data['status'] = 'err';
            }

            # title
            if ($title == null)
            {
                $data['error_reasons'][] = 'Kesinlikle başlık tespit edilemedi.';
                $data['status'] = 'err';
            }
            else if (strlen($title) < 6)
            {
                $data['error_reasons'][] = 'Başlık çok kısa.';
                $data['status'] = 'err';
            }
            else if (strlen($title) > 200)
            {
                $data['error_reasons'][] = 'Başlık çok uzun.';
                $data['status'] = 'err';
            }

            # description
            if ($description == null)
            {
                $data['error_reasons'][] = 'Kesinlikle açıklama tespit edilemedi.';
                $data['status'] = 'err';
            }
            else if (strlen($description) < 20)
            {
                $data['error_reasons'][] = 'Açıklama çok kısa.';
                $data['status'] = 'err';
            }
            else if (strlen($description) > 5000)
            {
                $data['error_reasons'][] = 'Açıklama çok uzun.';
                $data['status'] = 'err';
            }
        }
        catch (\Exception $e)
        {
            $data['status'] = 'failed';
            $data['error_reasons'][] = $e->getMessage();
        }

        return (object) $data;
    }

    /**
     * Ürün Tespiti
     *
     * @return array
     */
    public static function productDetection(string $site, string $page, array $selector, bool $proxy = false)
    {
        $selector = (object) $selector;

        $data = [
            'page' => $page,
            'status' => 'ok'
        ];

        $client = new Client([
            'base_uri' => $site,
            'handler' => HandlerStack::create()
        ]);

        try
        {
            $arr = [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))]
                ],
                'allow_redirects' => [
                    'max' => 4,
                    'strict' => true,
                    'referer' => true,
                    'protocols' => [ 'http', 'https' ],
                    'track_redirects' => true
                ],
                'curl' => [
                    CURLOPT_REFERER => $data['page']
                ],
                'verify' => false
            ];

            if ($proxy)
            {
                $p = Proxy::where('health', '>', 7)->inRandomOrder()->first();

                if (@$p)
                {
                    $arr['proxy'] = $p->proxy;
                }
            }

            $dom = $client->get($page, $arr)->getBody();

            $saw = new Wrawler($dom);

            # title detect
            $title = $saw->get($selector->title)->toText();
            $title = Term::convertAscii($title);

            # description detect
            $description = $saw->get($selector->description)->toText();
            $description = Term::convertAscii($description);

            # address detect
            $address = $saw->get($selector->address)->toArray();
            $address = array_map(function ($address) {
                return trim($address['#text'][0]);
            }, $address);

            # breadcrumb detect
            $breadcrumb = $saw->get($selector->breadcrumb)->toArray();
            $breadcrumb = array_map(function ($breadcrumb) {
                $text = trim($breadcrumb['#text'][0]);

                return $text ? $text : $breadcrumb['a'][0]['title'];
            }, $breadcrumb);

            # seller name detect
            $seller_name = $saw->get($selector->seller_name)->toText();
            $seller_name = Term::convertAscii($seller_name);

            # seller phones detect
            $seller_phones = $saw->get($selector->seller_phones)->toArray();
            $seller_phones = array_map(function ($seller_phones) {
                return trim($seller_phones['#text'][0]);
            }, $seller_phones);

            # price detect
            $price = null;
            $prce_raw = Term::convertAscii($saw->get($selector->price)->toText());

            $price_currency = substr(preg_replace('/([^a-zA-Z\$\€\₺]+)/', '', $prce_raw), 0, 2);
            $price_amount = intval(preg_replace('/([^\d]+)/', '', $prce_raw));

            if ($price_currency && $price_amount > 0)
            {
                $price = [
                    'currency' => $price_currency,
                    'amount' => $price_amount
                ];
            }

            # date
            $created_at = DateUtility::getDateInDom($dom);
            $created_at = $created_at ? $created_at : date('Y-m-d H:i:s');

            $data['data'] = [
                'title' => $title,
                'created_at' => $created_at,
                'address' => $address,
                'breadcrumb' => $breadcrumb,
                'seller_name' => $seller_name,
                'seller_phones' => $seller_phones
            ];

            if ($price)
            {
                $data['data']['price'] = $price;
            }
            else
            {
                $data['error_reasons'][] = 'Ücret tespit edilemedi.';
                $data['status'] = 'err';
            }

            if (!$created_at)
            {
                $data['error_reasons'][] = 'Tarih tespit edilemedi.';
                $data['status'] = 'err';
            }

            # title
            if ($title == null)
            {
                $data['error_reasons'][] = 'Başlık tespit edilemedi.';
                $data['status'] = 'err';
            }
            else if (strlen($title) > 155)
            {
                $data['error_reasons'][] = 'Başlık çok uzun.';
                $data['status'] = 'err';
            }

            # description
            if ($description == null)
            {
                $data['error_reasons'][] = 'Açıklama tespit edilemedi.';
            }
            else if (strlen($description) > 10000)
            {
                $data['error_reasons'][] = 'Açıklama çok uzun.';
                $data['status'] = 'err';
            }
            else
            {
                $data['data']['description'] = $description;
            }

            # address
            if (count($address) <= 1)
            {
                $data['error_reasons'][] = 'Adres tespit edilemedi.';
                $data['status'] = 'err';
            }

            # breadcrumb
            if (count($breadcrumb) <= 1)
            {
                $data['error_reasons'][] = 'Mini harita tespit edilemedi.';
                $data['status'] = 'err';
            }

            # seller name
            if ($seller_name == null)
            {
                $data['error_reasons'][] = 'Satıcı adı tespit edilemedi.';
                $data['status'] = 'err';
            }
            else if (strlen($seller_name) > 48)
            {
                $data['error_reasons'][] = 'Satıcı adı çok uzun.';
                $data['status'] = 'err';
            }
        }
        catch (\Exception $e)
        {
            $data['error_reasons'][] = $e->getMessage();
            $data['status'] = 'failed';
        }

        return (object) $data;
    }

    /**
     * Sözlük, Girdi Tespiti
     *
     * @return array
     */
    public static function entryDetection(string $site, string $page, int $id, string $title_selector, string $entry_selector, string $author_selector, bool $proxy = false)
    {
        $data['page'] = $site.'/'.str_replace('__id__', $id, $page);

        $client = new Client([
            'base_uri' => $site,
            'handler' => HandlerStack::create()
        ]);

        try
        {
            $arr = [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))]
                ],
                'curl' => [
                    CURLOPT_REFERER => $data['page']
                ],
                'verify' => false
            ];

            if ($proxy)
            {
                $p = Proxy::where('health', '>', 7)->inRandomOrder()->first();

                if (@$p)
                {
                    $arr['proxy'] = $p->proxy;
                }
            }

            $dom = $client->get($data['page'], $arr)->getBody();

            $saw = new Wrawler($dom);

            # title detect
            $title = $saw->get($title_selector)->toText();
            $title = Term::convertAscii($title);

            # entry detect
            $entry = $saw->get($entry_selector)->toText();
            $entry = Term::convertAscii($entry);

            # author detect
            $author = $saw->get($author_selector)->toText();
            $author = Term::convertAscii($author);

            # date
            $created_at = DateUtility::getDateInDom($dom, '/(\d{2}\.\d{2}\.\d{4})( \d{2}:\d{2}(:\d{2})?)?/');

            $data['data'] = [
                'title' => $title,
                'entry' => $entry,
                'author' => $author,
                'created_at' => $created_at
            ];

            $data['group_name'] = md5($title);
            $data['status'] = 'ok';

            if (!$created_at)
            {
                $data['error_reasons'][] = 'Tarih tespit edilemedi.';
                $data['status'] = 'err';
            }

            # title
            if ($title == null)
            {
                $data['error_reasons'][] = 'Başlık tespit edilemedi.';
                $data['status'] = 'err';
            }
            else if (strlen($title) > 155)
            {
                $data['error_reasons'][] = 'Başlık çok uzun.';
                $data['status'] = 'err';
            }

            # entry
            if ($entry == null)
            {
                $data['error_reasons'][] = 'Entry tespit edilemedi.';
                $data['status'] = 'err';
            }
            else if (strlen($entry) > 5000)
            {
                $data['error_reasons'][] = 'Entry çok uzun.';
                $data['status'] = 'err';
            }

            # author
            if ($author == null)
            {
                $data['error_reasons'][] = 'Yazar adı tespit edilemedi.';
                $data['status'] = 'err';
            }
            else if (strlen($author) > 64)
            {
                $data['error_reasons'][] = 'Yazar adı çok uzun.';
                $data['status'] = 'err';
            }
        }
        catch (\Exception $e)
        {
            $data['status'] = 'failed';
            $data['error_reasons'][] = $e->getMessage();
        }

        return (object) $data;
    }

    /**
     * Boş Sentiment
     *
     * @return array
     */
    public static function emptySentiment(array $arr)
    {
        return @$arr ? $arr : [
            'pos' => 0,
            'neg' => 0,
            'neu' => 1
        ];
    }
}
