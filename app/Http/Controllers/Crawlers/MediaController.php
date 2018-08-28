<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\Crawlers\Media\StatusRequest;
use App\Http\Requests\Crawlers\Media\UpdateRequest;

use App\Models\Crawlers\MediaCrawler;

use App\Jobs\Elasticsearch\CreateMediaIndexJob;
use App\Jobs\Elasticsearch\DropIndexJob;

use App\Utilities\Crawler;

use App\Elasticsearch\Indices;

class MediaController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list view
    # 
    public static function listView()
    {
        $count = MediaCrawler::count();

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

        $query = new MediaCrawler;
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
            $crawler = MediaCrawler::where('id', $id)->firstOrFail();
        }
        else
        {
            $crawler = new MediaCrawler;
            $crawler->name = 'Yeni Bot '.rand(999999999999, 9999999999999);
            $crawler->site = 'http://';
            $crawler->url_pattern = 'http://';
            $crawler->selector_title = '[itemprop="headline"]';
            $crawler->selector_description = '[itemprop="description"]';
            $crawler->save();

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
        $crawler = MediaCrawler::where('id', $request->id)->first();

        $data['status'] = 'err';

        $links = Crawler::linkDetection($request->site, $request->url_pattern, $request->base);

        $total = 0;
        $accepted = 0;

        if (@$links->links)
        {
            foreach ($links->links as $link)
            {
                if ($total < $request->test_count)
                {
                    $item = Crawler::articleDetection($request->site, $link, $request->selector_title, $request->selector_description);

                    $data['items'][] = $item;

                    if ($item->status == 'ok')
                    {
                        $accepted++;
                    }

                    $total++;
                }
            }

            $crawler->fill($request->all());

            if ($accepted > $total/2)
            {
                $crawler->test = true;

                $data['status'] = 'ok';

                CreateMediaIndexJob::dispatch($crawler->id);
            }

            $crawler->save();
        }
        else
        {
            $data['error_reasons'] = $links->error_reasons;
        }

        return $data;
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin status
    # 
    public static function status(StatusRequest $request)
    {
        $crawler = MediaCrawler::where('id', $request->id)->first();
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
}
