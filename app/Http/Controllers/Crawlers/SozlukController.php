<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\Crawlers\Sozluk\StatusRequest;
use App\Http\Requests\Crawlers\Sozluk\UpdateRequest;
use App\Http\Requests\Crawlers\Sozluk\DeleteRequest;

use App\Models\Crawlers\SozlukCrawler;

use App\Jobs\Elasticsearch\CreateSozlukIndexJob;
use App\Jobs\Elasticsearch\DeleteIndexJob;

use App\Utilities\Crawler;

use App\Elasticsearch\Indices;

class SozlukController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list view
    # 
    public static function listView()
    {
        return view('crawlers.sozluk.list');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # kelime list view
    # 
    public static function listViewJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new SozlukCrawler;
        $query = $request->string ? $query->where('name', 'ILIKE', '%'.$request->string.'%')
                                          ->orWhere('site', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('status', 'ASC')
                       ->orderBy('error_count', 'DESC')
                       ->orderBy('max_attempt', 'ASC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin global statistics
    # 
    public static function allStatistics()
    {
        $sozluk_crawler = new SozlukCrawler;

        return [
            'status' => 'ok',
            'data' => [
                'count' => [
                    'active' => $sozluk_crawler->where('status', true)->count(),
                    'disabled' => $sozluk_crawler->where('status', false)->count()
                ],
                'elasticsearch' => Indices::stats([ 'sozluk', '*' ])
            ]
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin global start all
    # 
    public static function allStart()
    {
        $crawlers = SozlukCrawler::where([
            'status' => false,
            'elasticsearch_index' => true,
            'test' => true
        ])->update([ 'status' => true ]);

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin global start all
    # 
    public static function allStop()
    {
        $crawlers = SozlukCrawler::where('status', true)->update([ 'status' => false ]);

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin global start all
    # 
    public static function allIndex()
    {
        $crawlers = SozlukCrawler::where('elasticsearch_index', false)->where('test', true)->get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                CreateSozlukIndexJob::dispatch($crawler->id)->onQueue('elasticsearch');
            }
        }

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin bot delete
    # 
    public static function delete(DeleteRequest $request)
    {
        $crawler = SozlukCrawler::where('id', $request->id)->delete();

        DeleteIndexJob::dispatch([ 'sozluk', $request->id ])->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin global statistics
    # 
    public static function statistics(int $id)
    {
        $crawler = SozlukCrawler::where('id', $id)->firstOrFail();

        return [
            'status' => 'ok',
            'data' => [
                'crawler' => $crawler,
                'elasticsearch' => Indices::stats([ 'sozluk', $crawler->id ])
            ]
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
            $crawler = SozlukCrawler::where('id', $id)->firstOrFail();
        }
        else
        {
            $crawler = new SozlukCrawler;
            $crawler->name = 'Yeni Bot '.rand(99999, 999999);
            $crawler->site = 'http://';
            $crawler->selector_title = 'h1#title';
            $crawler->selector_entry = '.content';
            $crawler->selector_author = '.entry-author';
            $crawler->save();

            return redirect()->route('crawlers.sozluk.bot', $crawler->id);
        }

        return view('crawlers.sozluk.view', compact('crawler'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin create
    # 
    public static function update(UpdateRequest $request)
    {
        $crawler = SozlukCrawler::where('id', $request->id)->first();

        $data['status'] = 'err';

        $total = 0;
        $accepted = 0;

        for ($i = $request->last_id; $i < $request->last_id+$request->test_count; $i++)
        {
            $item = Crawler::entryDetection(
                $request->site,
                $request->url_pattern,
                $i,
                $request->selector_title,
                $request->selector_entry,
                $request->selector_author
            );

            $data['items'][] = $item;

            if ($item->status == 'ok')
            {
                $accepted++;
            }

            $total++;
        }

        if ($accepted > $total/3)
        {
            $crawler->fill($request->all());
            $crawler->test = true;
            $crawler->error_count = 0;
            $crawler->off_reason = null;

            $data['status'] = 'ok';

            CreateSozlukIndexJob::dispatch($crawler->id)->onQueue('elasticsearch');
        }

        $crawler->save();

        return $data;
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin status
    # 
    public static function status(StatusRequest $request)
    {
        $crawler = SozlukCrawler::where('id', $request->id)->first();

        $crawler->status = $crawler->status ? 0 : 1;
        $crawler->save();

        return [
            'status' => 'ok',
            'data' => [
                'status' => $crawler->status
            ]
        ];
    }
}