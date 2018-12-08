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
                ]
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

                $proxy = Proxy::where('health', '>', 7)->inRandomOrder()->first();

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
    public static function articleDetection(string $site, string $page, string $title_selector, string $description_selector, bool $proxy = false)
    {
        $data['page'] = $page;

        $client = new Client([
            'base_uri' => $site,
            'handler' => HandlerStack::create()
        ]);

        $dateUtility = new DateUtility;

        try
        {
            $arr = [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))]
                ]
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

            $dom = str_replace([ '""' ], [ '"' ], $dom);

            $saw = new Wrawler($dom);

            $meta_property = $saw->get('meta[property]')->toArray();
            $meta_name = $saw->get('meta[name]')->toArray();

            # title detect
            $title = $saw->get($title_selector)->toText();

            if (!$title)
            {
                $data['status'] = 'err';
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
                $data['status'] = 'err';
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

            # date detect
            preg_match_all($dateUtility->datePattern(), $dom, $dates);

            $created_at = null;

            if (@$dates[0])
            {
                foreach ($dates[0] as $date)
                {
                    $date = $dateUtility->isDate($date);

                    if ($date)
                    {
                        $created_at = $date;

                        break;
                    }
                }
            }

            $data['data'] = [
                'title' => $title,
                'description' => $description,
                'created_at' => $created_at
            ];
            $data['status'] = 'ok';

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

    # article detection
    public static function productDetection(string $site, string $page, array $selector, bool $proxy = false)
    {
        $dateUtility = new DateUtility;

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
            $price = $saw->get($selector->price)->toText();
            $price_currency = preg_replace('/([^a-zA-Z\$\€\₺]+)/', '', $price);
            $price_amount = preg_replace('/([^\d]+)/', '', $price);

            if ($price_currency && strlen($price_currency) <= 3 && intval($price_amount) > 0)
            {
                $price = [
                    'currency' => $price_currency,
                    'amount' => intval($price_amount)
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

                if (!$price_amount || intval($price_amount) == 0)
                {
                    $data['error_reasons'][] = 'Ücret tutarı tespit edilemedi.';
                }
            }

            # date detect
            preg_match_all($dateUtility->datePattern(), $dom, $dates);

            $created_at = null;

            if (@$dates[0])
            {
                foreach ($dates[0] as $date)
                {
                    $date = $dateUtility->isDate($date);

                    if ($date)
                    {
                        $created_at = $date;

                        break;
                    }
                }
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

            # description
            if ($description == null)
            {
                //
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
            $data['status'] = 'failed';
            $data['error_reasons'][] = $e->getMessage();
        }

        return (object) $data;
    }

    # entry detection
    public static function entryDetection(string $site, string $page, int $id, string $title_selector, string $entry_selector, string $author_selector, bool $proxy = false)
    {
        $dateUtility = new DateUtility;

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

            # date detect
            preg_match_all('/(\d{2}\.\d{2}\.\d{4})( \d{2}:\d{2}(:\d{2})?)?/', $dom, $dates);

            $created_at = null;

            if (@$dates[0])
            {
                foreach ($dates[0] as $date)
                {
                    $date = $dateUtility->isDate($date);

                    if ($date)
                    {
                        $created_at = $date;

                        break;
                    }
                }
            }

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
            else if (strlen($author) > 32)
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
}
