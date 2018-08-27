<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\Crawlers\Media\StatusRequest;
use App\Http\Requests\Crawlers\Media\UpdateRequest;

use App\Models\Crawlers\MediaCrawler as Crawler;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Wrawler;
use App\Utilities\DateUtil;
use App\Utilities\Term;

use Carbon\Carbon;

class MediaController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list view
    # 
    public static function listView()
    {
        $count = Crawler::count();

        return view('crawlers.media.list', compact('count'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # kelime list view
    # 
    public static function listViewJson(SearchRequest $request)
    {
        $user = auth()->user();
        $organisation = $user->organisation;

        $take = $request->take;
        $skip = $request->skip;

        $query = new Crawler;
        $query = $request->string ? $query->where('name', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('status', 'ASC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin view
    # 
    public static function view(int $id = 0)
    {
        if ($id)
        {
            $crawler = Crawler::where('id', $id)->firstOrFail();
        }
        else
        {
            $crawler = new Crawler;
            $crawler->name = 'Yeni Bot '.rand(999999999999, 9999999999999);
            $crawler->link = 'http://';
            $crawler->pattern_url = '(http)...';
            $crawler->selector_title = 'h1[itemprop="headline"]';
            $crawler->selector_description = 'p[itemprop="description"]';
            $crawler->save();

            // index oluşturma isteği gönder...

            return redirect()->route('crawlers.media.bot', $crawler->id);
        }

        return view('crawlers.media.view', compact('crawler'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin create
    # 
    public static function update(UpdateRequest $request)
    {
        $crawler = Crawler::where('id', $request->id)->first();

        $data = [
            'count' => [
                'acceptable' => 0,
                'total' => 0
            ],
            'status' => 'warn',
            'errors' => []
        ];

        $client = new Client([
            'base_uri' => $request->link,
            'handler' => HandlerStack::create()
        ]);

        try
        {
            $dom = $client->get($request->base, [
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'User-Agent' => config('crawler.user_agent')
                ]
            ])->getBody();

            preg_match_all('/'.$request->pattern_url.'/', $dom, $match);

            if (@$match[0])
            {
                $match = array_unique($match[0]); 

                foreach ($match as $item)
                {
                    if ($data['count']['total'] < 10)
                    {
                        $data['count']['total'] = $data['count']['total']+1;

                        $page = $request->link.'/'.str_after($item, $request->link.'/');

                        $article_client = new Client([
                            'base_uri' => $request->link,
                            'handler' => HandlerStack::create()
                        ]);

                        try
                        {
                            $article_dom = $article_client->get($page, [
                                'timeout' => 10,
                                'connect_timeout' => 5,
                                'headers' => [
                                    'User-Agent' => config('crawler.user_agent')
                                ]
                            ])->getBody();

                            $article_dom = str_replace([ '""' ], [ '"' ], $article_dom);

                            $saw = new Wrawler($article_dom);

                            /*
                             * title detect
                             */
                            $title = $saw->get($request->selector_title)->toText();
                            $title = Term::convertAscii($title);
                            $title = strlen($title) > 6 ? $title : null;

                            /*
                             * description detect
                             */
                            $description = $saw->get($request->selector_description)->toText();
                            $description = Term::convertAscii($description);
                            $description = strlen($description) > 20 ? $description : null;

                            /*
                             * date detect
                             */
                            preg_match('/(\d{4}|\d{1,2})(\.|-| )(\d{1,2}|([a-zA-ZŞşıİğĞüÜ]{4,7}))(\.|-| )(\d{4}|\d{2})(( |, )[a-zA-ZÇçŞşığĞüÜ]{4,9})?(.| - )(\d{1,2}):(\d{1,2})(:(\d{1,2}))?((.?(\d{1,2}):(\d{1,2}))|Z)?/', $article_dom, $dates);

                            if (@$dates[0])
                            {
                                $yesterday = Carbon::now()->subDays(1)->format('Y-m-d H:i:s');

                                $created_at = DateUtil::isDate($dates[0]);
                                $created_at = $created_at > $yesterday ? $created_at : null;

                                if (!$created_at)
                                {
                                    $data['status'] = 'out_of_date';
                                }
                            }
                            else
                            {
                                $created_at = null;
                            }

                            if ($title && $description && $created_at)
                            {
                                $data['count']['acceptable'] = $data['count']['acceptable']+1;

                                $data['links'][] = [
                                    'title' => $title,
                                    'description' => $description,
                                    'created_at' => $created_at,
                                    'link' => $page
                                ];
                            }
                        }
                        catch (\Exception $e)
                        {
                            $data['errors'][]['reason'] = $e->getMessage();
                        }
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            $data['errors'][]['reason'] = $e->getMessage();
        }

        if ($data['count']['acceptable'] && $data['count']['acceptable'] >= ($data['count']['total']/2))
        {
            $crawler->fill($request->all());
            $crawler->test = true;
            $crawler->save();

            $data['status'] = 'ok';
            $data['type'] = $request->id ? 'created' : 'updated';
        }

        return $data;
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin status
    # 
    public static function status(StatusRequest $request)
    {
        $crawler = Crawler::where('id', $request->id)->first();
        $crawler->status = $crawler->status ? 0 : 1;

        if ($crawler->status)
        {
        	$crawler->error_count = 0;
        }

        $crawler->save();

        return [
        	'status' => 'ok',
        	'data' => [
        		'status' => $crawler->status
        	]
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin delete
    # 
    public static function delete(IdRequest $request)
    {
        $page = Crawler::where('id', $request->id)->delete();

        session()->flash('status', 'deleted');

        // index sil

        return [
            'status' => 'ok',
            'data' => [
            	'id' => $request->id
            ]
        ];
    }
}
