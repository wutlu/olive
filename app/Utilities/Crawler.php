<?php

namespace App\Utilities;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Wrawler;
use App\Utilities\DateUtility;
use App\Utilities\Term;

use Carbon\Carbon;

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
            $dom = $client->get($base, [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agent')
                ]
            ])->getBody();

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
            $dom = $client->get($page, [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agent')
                ]
            ])->getBody();

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
            $dom = $client->get($data['page'], [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agent')
                ]
            ])->getBody();

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
