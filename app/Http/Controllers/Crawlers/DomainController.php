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

use App\Utilities\Crawler;

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

        $name = str_replace(
            [
                'https://',
                'http://',
                'www.',
                '.com',
                '.tr',
                '.net',
                '.org',
                '.bel',
                '.space',
                '.gen',
                '.tv',
                '.nl',
                '.gov',
                '.co',
                '.uk',
                '.info',
                '.biz',
                '.web',
                '.aero',
                '.at',
                '.tc',
            ],
            '',
            $request->site
        );
        $name = str_replace('.', '-', $name);
        $data['data']['name'] = Term::convertAscii($name, [ 'uppercase' => true ]);

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
                                ->whereRaw('CHAR_LENGTH(url_pattern) > 24')
                                ->groupBy('url_pattern')
                                ->orderBy('total', 'DESC')
                                ->limit(50)
                                ->get();

        $urls = [];

        foreach ($patterns as $pattern)
        {
            $links = Crawler::linkInDom($request->site, $pattern->url_pattern, $dom);

            if ($links->status == 'ok' && count($links->data))
            {
                $urls[] = [
                    'count' => count($links->data),
                    'pattern' => $pattern->url_pattern,
                    'url' => $links->data[0]
                ];
            }
        }

        asort($urls);

        $urls = array_reverse($urls);

        /* --- */

        foreach ($urls as $key => $url)
        {
            if ($url['count'] >= 20 && $url['count'] <= 600)
            {
                $data['data']['url_pattern'] = $url['pattern'];

                try
                {
                    $source = $client->get($url['url'], $arr)->getBody();
                    $source = str_replace('&nbsp;', ' ', $source);

                    $saw = new Wrawler($source);

                    $selectors = [
                        'h1.entry-title',
                        '.entry-title',
                        'h1.title',
                        'h1.content-title',
                        'h1[itemprop="name"]',
                        'h1[itemprop="headline"]',
                        '.panel-title > h1',
                        'h1.detail-post-title',
                        '.haber_ayrinti_baslik',
                        'h1.baslik',
                        'h1.jeg_post_title',
                        '.single_title',
                        'h3.title',
                        'h1.news-title',
                        '.Baslik h1',
                        '.haberBaslik > h1',
                        '.haberBaslik',
                        '.news-title > h1',
                        'h1.mainHeading',
                        '.haber-baslik',
                        'h1#haber_baslik',
                        'h1.post-title',
                        'h2.title',
                        '.panel-title h1',
                        'h1.hbr-baslik',
                        '#kapsayici > h1',
                        'header h1',
                        'h1.h1class',
                        'h1.pageTitle',
                        'h2[itemprop="headline"]',
                        'h1.single-post-title',
                        '.haber-ust h1',
                        'h1.singular_title_v2',
                    ];

                    foreach ($selectors as $selector)
                    {
                        $title = $saw->get($selector)->toText();
                        $title = Term::convertAscii($title);

                        if (strlen($title) >= 16 && strlen($title) <= 100)
                        {
                            $data['data']['title'] = $selector;
                            break;
                        }
                    }

                    $selectors = [
                        '.entry-content > p:nth-child(1)',
                        '.entry-content p:nth-child(1)',
                        'p.lead',
                        '.panel-title > h2',
                        '.text_post_block > h2',
                        '.haber_ayrinti_spot',
                        'h2.lead',
                        '.lead',
                        'h2.detail-post-spot',
                        'h2.jeg_post_subtitle',
                        'p.spot',
                        '.spot',
                        '.short_content',
                        '.description',
                        '#iceriks p:nth-child(1)',
                        'p[itemprop="description"]',
                        '.haberSpot',
                        '.summary > h2',
                        'header > h2',
                        '.haberText p:nth-child(1)',
                        '#haberdetaybaslik > h2',
                        'h1.news-detail-title',
                        '.td-post-content > p:nth-child(1)',
                        '.td-post-content p:nth-child(1)',
                        '#singleContent > p:nth-child(1)',
                        'h2.content-description',
                        '.panel-title > p',
                        'h2[itemprop="description"]',
                        '#singleContent p:nth-child(1)',
                        '.panel-title p:nth-child(1)',
                        '.haber_ayrinti_detay p:nth-child(1)',
                        'header h2',
                        'h2.entry-sub-title',
                        '.icerik_detay p:nth-child(1)',
                        'h2.hdesc',
                        'p.brief',
                        '.icerik > p:nth-child(1)',
                        '.entry-content h2',
                        '.haber-icerik p:nth-child(1)',
                        '.ozet > h2',
                        '.hbr-metin',
                        '#spot',
                        '.content_inner_section_salt > p:nth-child(1)'
                    ];

                    foreach ($selectors as $selector)
                    {
                        $description = $saw->get($selector)->toText();
                        $description = Term::convertAscii($description);

                        if (strlen($description) >= 64 && strlen($description) <= 500)
                        {
                            $data['data']['description'] = $selector;
                            break;
                        }
                    }
                }
                catch (\Exception $e)
                {
                    $data['error_reasons'][] = $e->getMessage();
                }
            }

            if (@$data['data']['title'] && @$data['data']['description'])
            {
                break;
            }
        }

        /* --- */

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
