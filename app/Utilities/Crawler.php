<?php

namespace App\Utilities;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Cookie\CookieJar;

use App\Wrawler;
use App\Utilities\DateUtility;
use App\Utilities\Term;

use Carbon\Carbon;

use App\Models\Proxy;

use App\Utilities\ImageUtility;

class Crawler
{
    /**
     * Makale, Bağlantı Tespiti
     *
     * @return object
     */
    public static function articleLinkDetection(string $site, string $url_pattern = null, string $base, bool $standard, bool $proxy)
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
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                    'Accept-Language' => 'tr-TR;q=0.6,tr;q=0.4'
                ],
                'curl' => [
                    CURLOPT_REFERER => $site,
                    CURLOPT_COOKIE => 'AspxAutoDetectCookieSupport=1'
                ],
                'verify' => false,
                'allow_redirects' => [
                    'max' => 6
                ]
            ];

            if ($standard)
            {
                $arr['headers']['Accept'] = 'application/xml';
            }

            if ($proxy)
            {
                $p = Proxy::where('ipv', 4)->where('health', '>', 7)->inRandomOrder()->first();

                if (@$p)
                {
                    $arr['proxy'] = $p->proxy;
                }
            }

            $dom = $client->get($base, $arr)->getBody();

            if ($standard)
            {
                $xml = new \SimpleXMLElement($dom);
                $feed = (array) $xml->channel;

                foreach ($feed['item'] as $item)
                {
                    $data['links'][] = ((array) $item->link)[0];
                }
            }
            else
            {
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
     * @return object
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
                        'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                        'Accept-Language' => 'tr-TR;q=0.6,tr;q=0.4'
                    ],
                    'verify' => false,
                    'allow_redirects' => [
                        'max' => 6
                    ]
                ];

                $dom = $client->get('https://www.google.com/search?q='.$query.'&tbs=qdr:'.$google_time.',sbd:1&start='.$page, $arr)->getBody();

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
     * Google Arama Sonucu, Sonuç Sayısı
     *
     * @return object
     */
    public static function googleSearchCount(string $title)
    {
        $data = [];

        $client = new Client([
            'base_uri' => 'https://www.google.com',
            'handler' => HandlerStack::create()
        ]);

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

            $dom = $client->get('https://www.google.com/search?q="'.$title.'"&tbs=qdr:h,sbd:1', $arr)->getBody();

            $saw = new Wrawler($dom);

            $stats = $saw->get('#resultStats')->toArray();
            $count = intval(preg_replace('/[^\d]/', '', $stats[0]['#text'][0]));

            return $count;
        }
        catch (\Exception $e)
        {
            return 0;
        }
    }

    /**
     * Makale Tespiti
     *
     * @return object
     */
    public static function articleDetection(string $site, string $page, string $title_selector = null, string $description_selector = null, bool $standard, bool $proxy)
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
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                    'Accept-Language' => 'tr-TR;q=0.6,tr;q=0.4'
                ],
                'curl' => [
                    CURLOPT_REFERER => $data['page'],
                    CURLOPT_COOKIE => 'AspxAutoDetectCookieSupport=1'
                ],
                'verify' => false
            ];

            if ($proxy)
            {
                $p = Proxy::where('ipv', 4)->where('health', '>', 7)->inRandomOrder()->first();

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

            if ($standard)
            {
                $title = null;
            }
            else
            {
                # title detect
                $title = $saw->get($title_selector)->toText();
            }

            if (!$title)
            {
                if (!$standard)
                {
                    $data['error_reasons'][] = 'Başlık tespit edilemedi. Alternatif denendi.';
                }

                $title = @array_first($meta_property, function ($value, $key) { return @$value['property'] == 'og:title'; })['content'];

                if (!$title)
                {
                    $title = @array_first($meta_name, function ($value, $key) { return @$value['name'] == 'twitter:title'; })['content'];
                }
            }

            $title = Term::convertAscii($title);

            if ($standard)
            {
                $description = null;
            }
            else
            {
                # description detect
                $description = $saw->get($description_selector)->toText();
            }

            if (!$description)
            {
                if (!$standard)
                {
                    $data['error_reasons'][] = 'Açıklama tespit edilemedi. Alternatif denendi.';
                }

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

            $image = @array_first($meta_property, function ($value, $key) { return @$value['property'] == 'og:image'; })['content'];

            if (!$image)
            {
                $image = @array_first($meta_name, function ($value, $key) { return @$value['name'] == 'twitter:image'; })['content'];
            }

            if (!$image)
            {
                $image = @array_first($meta_name, function ($value, $key) { return @$value['itemprop'] == 'image'; })['content'];
            }

            if ($image)
            {
                if (!filter_var($image, FILTER_VALIDATE_URL))
                {
                    $image = null;
                }
            }

            $created_at = DateUtility::getDateInDom($dom);

            $data['data'] = [
                'title' => $title,
                'description' => $description,
                'created_at' => $created_at,
            ];

            # image
            if ($image)
            {
                $data['data']['image_url'] = $image;
            }
            else
            {
                $data['error_reasons'][] = 'Resim tespit edilemedi.';
            }

            # date
            if (!$created_at)
            {
                $data['error_reasons'][] = 'Tarih tespit edilemedi. Alternatif denendi.';
                $data['data']['created_at'] = date('Y-m-d H:i:00');
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
     * @return object
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
                    'User-Agent' => config('crawler.user_agents')[array_rand(config('crawler.user_agents'))],
                    'Accept-Language' => 'tr-TR;q=0.6,tr;q=0.4'
                ],
                'allow_redirects' => [
                    'max' => 4,
                    'strict' => true,
                    'referer' => true,
                    'protocols' => [ 'http', 'https' ],
                    'track_redirects' => true
                ],
                'curl' => [
                    CURLOPT_REFERER => $data['page'],
                    CURLOPT_COOKIE => 'AspxAutoDetectCookieSupport=1'
                ],
                'verify' => false
            ];

            if ($proxy)
            {
                $p = Proxy::where('ipv', 4)->where('health', '>', 7)->inRandomOrder()->first();

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

                return $text ? $text : trim($breadcrumb['a'][0]['#text'][0]);
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
                'created_at' => $created_at
            ];

            if ($price)
            {
                $data['data']['price'] = $price;
            }
            else
            {
                $data['error_reasons'][] = 'Ücret tespit edilemedi.';
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
            else if (strlen($title) > 200)
            {
                $data['error_reasons'][] = 'Başlık çok uzun.';
                $data['status'] = 'err';
            }

            # description
            if ($description == null)
            {
                $data['error_reasons'][] = 'Açıklama tespit edilemedi.';
            }
            else if (strlen($description) > 5000)
            {
                $data['error_reasons'][] = 'Açıklama çok uzun.';
                $data['status'] = 'err';
            }
            else
            {
                $data['data']['description'] = $description;
            }

            # address
            if (count($address))
            {
                if (count($address) == 1)
                {
                    if (substr_count($address[0], '/'))
                    {
                        $address = explode('/', $address[0]);
                    }
                }

                $data['data']['address'] = array_map(function($arr) {
                    return trim(title_case($arr));
                }, $address);
            }
            else
            {
                $data['error_reasons'][] = 'Adres tespit edilemedi.';
            }

            # breadcrumb
            if (count($breadcrumb))
            {
                $data['data']['breadcrumb'] = $breadcrumb;
            }
            else
            {
                $data['error_reasons'][] = 'Mini harita tespit edilemedi.';
            }

            # seller name
            if ($seller_name == null)
            {
                $data['error_reasons'][] = 'Satıcı adı tespit edilemedi.';
            }
            else if (strlen($seller_name) > 64)
            {
                $data['error_reasons'][] = 'Satıcı adı çok uzun.';
            }
            else
            {
                $data['data']['seller_name'] = $seller_name;
            }

            if (count($seller_phones))
            {
                $data['data']['seller_phones'] = $seller_phones;
            }
            else
            {
                $data['error_reasons'][] = 'Satıcı telefon numarası tespit edilemedi.';
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
     * @return object
     */
    public static function entryDetection(array $options, int $id, bool $proxy = false)
    {
        $data['page'] = $options['site'].'/'.str_replace('__id__', $id, $options['url_pattern']);

        $client = new Client([
            'base_uri' => $options['site'],
            'handler' => HandlerStack::create(),
            'cookies' => true
        ]);

        try
        {
            $arr = [
                'timeout' => 10,
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

            if ($options['cookie'])
            {
                $cookieJar = CookieJar::fromArray(
                    $options['cookie']['cookies'],
                    $options['cookie']['domain']
                );

                $arr['cookies'] = $cookieJar;
            }

            if ($proxy)
            {
                $p = Proxy::where('ipv', 4)->where('health', '>', 7)->inRandomOrder()->first();

                if (@$p)
                {
                    $arr['proxy'] = $p->proxy;
                }
            }

            $dom = $client->get($data['page'], $arr)->getBody();

            $saw = new Wrawler($dom);

            # title detect
            $title = $saw->get($options['selector_title'])->toText();
            $title = Term::convertAscii($title);

            # entry detect
            $entry = $saw->get($options['selector_entry'])->toText();
            $entry = Term::convertAscii($entry);

            # author detect
            $author = $saw->get($options['selector_author'])->toText();
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
    public static function emptySentiment($arr = [])
    {
        return @$arr ? $arr : [
            'pos' => 0,
            'neg' => 0,
            'neu' => 1
        ];
    }
}
