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
            preg_match('/(\d{4}|\d{1,2})(\.|-| )(\d{1,2}|([a-zA-ZŞşıİğĞüÜ]{4,8}))(\.|-| )(\d{4}|\d{2})(( |, )[a-zA-ZÇçŞşığĞüÜ]{4,10})?(.| - )(\d{1,2}):(\d{1,2})(:(\d{1,2}))?((.?(\d{1,2}):(\d{1,2}))|Z)?/', $dom, $dates);

            if (@$dates[0])
            {
                $yesterday = Carbon::now()->subDays(2)->format('Y-m-d H:i:s');

                $created_at = DateUtility::isDate($dates[0]);

                # date
                if ($created_at < $yesterday)
                {
                    $data['error_reasons'][] = 'Tarih güncel değil.';
                }
            }
            else
            {
                $created_at = null;
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
            else if (strlen($description) > 1500)
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
            $data['status'] = 'err';
            $data['error_reasons'][] = $e->getMessage();
        }

        return (object) $data;
    }
}
