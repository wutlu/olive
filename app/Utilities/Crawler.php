<?php

namespace App\Utilities;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Wrawler;
use App\Utilities\DateUtility;
use App\Utilities\Term;

use Carbon\Carbon;

use App\Models\Proxy;

class Crawler
{
    # link detection
    public static function linkDetection(string $site, string $url_pattern, string $base = '/')
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
                ]
            ];

            $proxy = Proxy::where('health', '>', 5)->inRandomOrder()->first();

            if (@$proxy)
            {
                $arr['proxy'] = $proxy->proxy;
            }

            $dom = $client->get($base, $arr)->getBody();

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

    # google search result link detection
    public static function googleSearchResultLinkDetection(string $site, string $url_pattern, string $query, int $max_page = 1)
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
                    ]
                ];

                $proxy = Proxy::where('health', '>', 5)->inRandomOrder()->first();

                if (@$proxy)
                {
                    $arr['proxy'] = $proxy->proxy;
                }

                $dom = $client->get('https://www.google.com/search?q='.$query.'&tbs=qdr:h,sbd:1&start='.$page, $arr)->getBody();

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

    # article detection
    public static function articleDetection(string $site, string $page, string $title_selector, string $description_selector)
    {
        $data['page'] = $page;

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
                ]
            ];

            $proxy = Proxy::where('health', '>', 5)->inRandomOrder()->first();

            if (@$proxy)
            {
                $arr['proxy'] = $proxy->proxy;
            }

            $dom = $client->get($page, $arr)->getBody();

            $dom = str_replace([ '""' ], [ '"' ], $dom);

            $saw = new Wrawler($dom);

            # title detect
            $title = $saw->get($title_selector)->toText();
            $title = Term::convertAscii($title);

            # description detect
            $description = $saw->get($description_selector)->toText();
            $description = Term::convertAscii($description);

            # date detect
            preg_match_all('/(\d{4}|\d{1,2})(\.|-| )(\d{1,2}|([a-zA-ZŞşıİğĞüÜ]{4,8}))(\.|-| )(\d{4}|\d{2})(( |, )[a-zA-ZÇçŞşığĞüÜ]{4,10})?((.| - )(\d{1,2}):(\d{1,2})(:(\d{1,2}))?((.?(\d{1,2}):(\d{1,2}))|Z)?)?/', $dom, $dates);

            $created_at = null;

            if (@$dates[0])
            {
                foreach ($dates[0] as $date)
                {
                    $date = DateUtility::isDate($date);

                    if ($date)
                    {
                        $created_at = $date;

                        break;
                    }
                }
            }

            if (!$created_at)
            {
                $data['error_reasons'][] = 'Tarih tespit edilemedi.';
            }

            $data['data'] = [
                'title' => $title,
                'description' => $description,
                'created_at' => $created_at
            ];

            # title
            if ($title == null)
            {
                $data['error_reasons'][] = 'Başlık tespit edilemedi.';
            }
            else if (strlen($title) < 6)
            {
                $data['error_reasons'][] = 'Başlık çok kısa.';
            }
            else if (strlen($title) > 155)
            {
                $data['error_reasons'][] = 'Başlık çok uzun.';
            }

            # description
            if ($description == null)
            {
                $data['error_reasons'][] = 'Açıklama tespit edilemedi.';
            }
            else if (strlen($description) < 20)
            {
                $data['error_reasons'][] = 'Açıklama çok kısa.';
            }
            else if (strlen($description) > 5000)
            {
                $data['error_reasons'][] = 'Açıklama çok uzun.';
            }

            if (count(@$data['error_reasons']))
            {
                $data['status'] = 'err';
            }
            else
            {
                $data['status'] = 'ok';
            }
        }
        catch (\Exception $e)
        {
            $data['status'] = 'failed';
            $data['error_reasons'][] = $e->getMessage();
        }

        return (object) $data;
    }

    # article detection
    public static function productDetection(string $site, string $page, array $selector)
    {
        $selector = (object) $selector;

        $data['page'] = $page;

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
                ]
            ];

            $proxy = Proxy::where('health', '>', 5)->inRandomOrder()->first();

            if (@$proxy)
            {
                $arr['proxy'] = $proxy->proxy;
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
            $price = $saw->get($selector->price)->toText();
            $price_currency = preg_replace('/([^a-zA-Z\$\€\₺]+)/', '', $price);
            $price_quantity = preg_replace('/([^\d]+)/', '', $price);

            if ($price_currency && strlen($price_currency) <= 3 && intval($price_quantity) > 0)
            {
                $price = [
                    'currency' => $price_currency,
                    'quantity' => intval($price_quantity)
                ];
            }
            else
            {
                if (!$price_currency)
                {
                    $data['error_reasons'][] = 'Ücret birimi tespit edilemedi.';
                }
                else if (strlen($price_currency) > 3)
                {
                    $data['error_reasons'][] = 'Ücret birimi geçersiz. ('.$price_currency.')';
                }

                if (!$price_quantity || intval($price_quantity) == 0)
                {
                    $data['error_reasons'][] = 'Ücret miktarı tespit edilemedi.';
                }
            }

            # date detect
            preg_match_all('/(\d{4}|\d{1,2})(\.|-| )(\d{1,2}|([a-zA-ZŞşıİğĞüÜ]{4,8}))(\.|-| )(\d{4}|\d{2})(( |, )[a-zA-ZÇçŞşığĞüÜ]{4,10})?((.| - )(\d{1,2}):(\d{1,2})(:(\d{1,2}))?((.?(\d{1,2}):(\d{1,2}))|Z)?)?/', $dom, $dates);

            $created_at = null;

            if (@$dates[0])
            {
                foreach ($dates[0] as $date)
                {
                    $date = DateUtility::isDate($date);

                    if ($date)
                    {
                        $created_at = $date;

                        break;
                    }
                }
            }

            if (!$created_at)
            {
                $data['error_reasons'][] = 'Tarih tespit edilemedi.';
            }

            $data['data'] = [
                'title' => $title,
                'created_at' => $created_at,
                'address' => $address,
                'breadcrumb' => $breadcrumb,
                'seller_name' => $seller_name,
                'seller_phones' => $seller_phones,
                'price' => $price
            ];

            # title
            if ($title == null)
            {
                $data['error_reasons'][] = 'Başlık tespit edilemedi.';
            }
            else if (strlen($title) > 155)
            {
                $data['error_reasons'][] = 'Başlık çok uzun.';
            }

            # description
            if ($description == null)
            {
                //
            }
            else if (strlen($description) > 10000)
            {
                $data['error_reasons'][] = 'Açıklama çok uzun.';
            }
            else
            {
                $data['data']['description'] = $description;
            }

            # address
            if (count($address) <= 1)
            {
                $data['error_reasons'][] = 'Adres tespit edilemedi.';
            }

            # breadcrumb
            if (count($breadcrumb) <= 1)
            {
                $data['error_reasons'][] = 'Mini harita tespit edilemedi.';
            }

            # seller name
            if ($seller_name == null)
            {
                $data['error_reasons'][] = 'Satıcı adı tespit edilemedi.';
            }
            else if (strlen($seller_name) > 48)
            {
                $data['error_reasons'][] = 'Satıcı adı çok uzun.';
            }

            if (count(@$data['error_reasons']))
            {
                $data['status'] = 'err';
            }
            else
            {
                $data['status'] = 'ok';
            }
        }
        catch (\Exception $e)
        {
            $data['status'] = 'failed';
            $data['error_reasons'][] = $e->getMessage();
        }

        return (object) $data;
    }

    # entry detection
    public static function entryDetection(string $site, string $page, int $id, string $title_selector, string $entry_selector, string $author_selector)
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
                ]
            ];

            $proxy = Proxy::where('health', '>', 5)->inRandomOrder()->first();

            if (@$proxy)
            {
                $arr['proxy'] = $proxy->proxy;
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

            # date detect
            preg_match_all('/(\d{2}\.\d{2}\.\d{4})( \d{2}:\d{2}(:\d{2})?)?/', $dom, $dates);

            $created_at = null;

            if (@$dates[0])
            {
                foreach ($dates[0] as $date)
                {
                    $date = DateUtility::isDate($date);

                    if ($date)
                    {
                        $created_at = $date;

                        break;
                    }
                }
            }

            if (!$created_at)
            {
                $data['error_reasons'][] = 'Tarih tespit edilemedi.';
            }

            $data['data'] = [
                'title' => $title,
                'entry' => $entry,
                'author' => $author,
                'created_at' => $created_at
            ];

            # title
            if ($title == null)
            {
                $data['error_reasons'][] = 'Başlık tespit edilemedi.';
            }
            else if (strlen($title) > 155)
            {
                $data['error_reasons'][] = 'Başlık çok uzun.';
            }

            # entry
            if ($entry == null)
            {
                $data['error_reasons'][] = 'Entry tespit edilemedi.';
            }
            else if (strlen($entry) > 5000)
            {
                $data['error_reasons'][] = 'Entry çok uzun.';
            }

            # author
            if ($author == null)
            {
                $data['error_reasons'][] = 'Yazar adı tespit edilemedi.';
            }
            else if (strlen($author) > 32)
            {
                $data['error_reasons'][] = 'Yazar adı çok uzun.';
            }

            if (count(@$data['error_reasons']))
            {
                $data['status'] = 'err';
            }
            else
            {
                $data['group_name'] = md5($title);
                $data['status'] = 'ok';
            }
        }
        catch (\Exception $e)
        {
            $data['status'] = 'failed';
            $data['error_reasons'][] = $e->getMessage();
        }

        return (object) $data;
    }
}
