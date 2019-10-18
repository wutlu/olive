<?php

namespace App\Http\Controllers\Crawlers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Http\Controllers\Controller;

use App\Models\DetectedDomains;
use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\BlogCrawler;

use App\Http\Requests\IdRequest;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Models\Proxy;

use App\Wrawler;
use Term;

class DomainController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Domain, bot detay tespiti
     *
     * @return view
     */
    public static function siteDetect(Request $request)
    {
        $data = [
            'status' => 'ok'
        ];

        $request->validate([
            'site' => 'required|string|url|max:155',
            'base' => 'required|string|max:100',
            'proxy' => 'nullable|string|in:on'
        ]);

        $client = new Client([
            'base_uri' => $request->site,
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
                    CURLOPT_REFERER => $request->site,
                    CURLOPT_COOKIE => 'AspxAutoDetectCookieSupport=1'
                ],
                'verify' => false,
                'allow_redirects' => [
                    'max' => 6
                ]
            ];

            if ($request->proxy)
            {
                $p = Proxy::where('ipv', 4)->where('health', '>', 7)->inRandomOrder()->first();

                if (@$p)
                {
                    $arr['proxy'] = $p->proxy;
                }
            }

            $dom = $client->get($request->base, $arr)->getBody();

            $patterns = MediaCrawler::select('url_pattern', \DB::raw('count(*) as total'))
                                    ->whereNotNull('url_pattern')
                                    ->groupBy('url_pattern')
                                    ->orderByRaw('CHAR_LENGTH(url_pattern) DESC')
                                    ->get();

            foreach ($patterns as $pattern)
            {
                preg_match_all('/'.$pattern->url_pattern.'/', $dom, $match);

                if (@$match[0])
                {
                    $match = array_values(array_unique($match[0])); 

                    if (count($match) >= 20 && count($match) <= 600)
                    {
                        $data['data']['url_pattern'] = $pattern->url_pattern;

                        $source = $client->get($match[10], $arr)->getBody();

                        $source = str_replace('&nbsp;', ' ', $source);

                        $saw = new Wrawler($source);

                        $selectors = MediaCrawler::select('selector_title', \DB::raw('count(*) as total'))
                                                 ->whereNotNull('selector_title')
                                                 ->groupBy('selector_title')
                                                 ->orderBy('total', 'DESC')
                                                 ->get();

                        foreach ($selectors as $selector)
                        {
                            $title = $saw->get($selector->selector_title)->toText();
                            $title = Term::convertAscii($title);

                            if (strlen($title) >= 16 && strlen($title) <= 100)
                            {
                                $data['data']['title'] = $selector->selector_title;
                                break;
                            }
                        }

                        $selectors = MediaCrawler::select('selector_description', \DB::raw('count(*) as total'))
                                                 ->whereNotNull('selector_description')
                                                 ->groupBy('selector_description')
                                                 ->orderBy('total', 'DESC')
                                                 ->get();

                        foreach ($selectors as $selector)
                        {
                            $description = $saw->get($selector->selector_description)->toText();

                            $description = Term::convertAscii($description);

                            if (strlen($description) >= 64 && strlen($description) <= 500)
                            {
                                $data['data']['description'] = $selector->selector_description;
                                break;
                            }
                        }

                        break;
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            $data['error_reasons'][] = $e->getMessage();
        }

        return $data;
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Domain, ana sayfası.
     *
     * @return view
     */
    public static function dashboard(Request $request, int $pager = 50)
    {
        $request->validate([
            'q' => 'nullable|string|max:100'
        ]);

        $user = auth()->user();

        $data = new DetectedDomains;

        if ($request->q)
        {
            $data = $data->where('domain', 'ILIKE', '%'.$request->q.'%');
        }

        $data = $data->orderBy('id', 'DESC')->orderBy('status', 'ASC')->paginate($pager);

        $q = $request->q;

        if ($data->total() > $pager && count($data) == 0)
        {
            return redirect()->route('crawlers.domain');
        }

        return view('crawlers.domain', compact('data', 'q', 'pager'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Domain, durum kontrolü.
     *
     * @return array
     */
    public static function check(IdRequest $request)
    {
        $query = DetectedDomains::where('id', $request->id)->firstOrFail();

        $message = null;

        $key = str_replace([ 'https://', 'http://', 'www.' ], '', $query->domain);

        $explode = explode('.', $key);

        if (count($explode) >= 3 && strlen(end($explode)) != 2)
        {
            unset($explode[0]);
        }

        $key = implode('\.', $explode);

        $media_crawler = MediaCrawler::orWhere('site', '~*', '^(http(s)?:\/\/((www|mobile|m|mobil)\.)?'.$key.')$')->exists();
        $blog_crawler = BlogCrawler::orWhere('site', '~*', '^(http(s)?:\/\/((www|mobile|m|mobil)\.)?'.$key.')$')->exists();
        $sozluk_crawler = SozlukCrawler::orWhere('site', '~*', '^(http(s)?:\/\/((www|mobile|m|mobil)\.)?'.$key.')$')->exists();

        if ($media_crawler)
        {
            $query->module = 'news';
            $query->status = 'ok';

            $message = 'Haber kaynağı olarak takipte.';
        }
        else if ($blog_crawler)
        {
            $query->module = 'blog';
            $query->status = 'ok';

            $message = 'Blog kaynağı olarak takipte.';
        }
        else if ($sozluk_crawler)
        {
            $query->module = 'sozluk';
            $query->status = 'ok';

            $message = 'Sözlük kaynağı olarak takipte.';
        }
        else
        {
            $query->status = 'err';

            $message = 'Bağlantı hiçbir modülden takip edilmiyor.';
        }

        $query->save();

        return [
            'status' => 'ok',
            'data' => [
                'color' => $query->color(),
                'message' => $message
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Domain, Sil
     *
     * @return array
     */
    public static function delete(IdRequest $request)
    {
        $query = DetectedDomains::where('id', $request->id)->delete();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id
            ]
        ];
    }
}
