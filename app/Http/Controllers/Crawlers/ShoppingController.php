<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\Crawlers\Shopping\StatusRequest;
use App\Http\Requests\Crawlers\Shopping\UpdateRequest;
use App\Http\Requests\Crawlers\Shopping\DeleteRequest;

use App\Models\Crawlers\ShoppingCrawler;

use App\Jobs\Elasticsearch\CreateShoppingIndexJob;
use App\Jobs\Elasticsearch\DeleteIndexJob;

use App\Utilities\Crawler;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

class ShoppingController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list view
    # 
    public static function listView()
    {
        return view('crawlers.shopping.list');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # kelime list view
    # 
    public static function listViewJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new ShoppingCrawler;
        $query = $request->string ? $query->where('name', 'ILIKE', '%'.$request->string.'%')
                                          ->orWhere('site', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('status', 'ASC')
                       ->orderBy('error_count', 'DESC')
                       ->orderBy('control_interval', 'ASC');

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
        $shopping_crawler = new ShoppingCrawler;
        $document = new Document;

        return [
            'status' => 'ok',
            'data' => [
                'count' => [
                    'active' => $shopping_crawler->where('status', true)->count(),
                    'disabled' => $shopping_crawler->where('status', false)->count(),
                    'buffer' => $document->count([ 'shopping', '*' ], 'product', [
                        'query' => [
                            'match' => [
                                'status' => 'buffer'
                            ]
                        ]
                    ]),
                    'success' => $document->count([ 'shopping', '*' ], 'product', [
                        'query' => [
                            'match' => [
                                'status' => 'ok'
                            ]
                        ]
                    ]),
                    'failed' => $document->count([ 'shopping', '*' ], 'product', [
                        'query' => [
                            'match' => [
                                'status' => 'failed'
                            ]
                        ]
                    ])
                ],
                'elasticsearch' => Indices::stats([ 'shopping', '*' ])
            ]
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin global start all
    # 
    public static function allStart()
    {
        $crawlers = ShoppingCrawler::where([
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
        $crawlers = ShoppingCrawler::where('status', true)->update([ 'status' => false ]);

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
        $crawlers = ShoppingCrawler::where('elasticsearch_index', false)->where('test', true)->get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                CreateShoppingIndexJob::dispatch($crawler->id)->onQueue('elasticsearch');
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
        $crawler = ShoppingCrawler::where('id', $request->id)->delete();

        DeleteIndexJob::dispatch([ 'shopping', $request->id ])->onQueue('elasticsearch');

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
        $crawler = ShoppingCrawler::where('id', $id)->firstOrFail();
        $document = new Document;

        return [
            'status' => 'ok',
            'data' => [
                'crawler' => $crawler,
                'count' => [
                    'buffer' => $document->count([ 'shopping', $crawler->id ], 'product', [
                        'query' => [
                            'match' => [
                                'status' => 'buffer'
                            ]
                        ]
                    ]),
                    'success' => $document->count([ 'shopping', $crawler->id ], 'product', [
                        'query' => [
                            'match' => [
                                'status' => 'ok'
                            ]
                        ]
                    ]),
                    'failed' => $document->count([ 'shopping', $crawler->id ], 'product', [
                        'query' => [
                            'match' => [
                                'status' => 'failed'
                            ]
                        ]
                    ])
                ],
                'elasticsearch' => Indices::stats([ 'shopping', $crawler->id ])
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
            $crawler = ShoppingCrawler::where('id', $id)->firstOrFail();
        }
        else
        {
            $crawler = new ShoppingCrawler;
            $crawler->name = 'Yeni Bot '.rand(99999, 999999);
            $crawler->site = 'http://';
            $crawler->url_pattern = '([a-z0-9-]{4,128})';
            $crawler->selector_title = 'x';
            $crawler->selector_description = 'x';
            $crawler->selector_categories = 'x';
            $crawler->selector_address = 'x';
            $crawler->selector_ul = 'x';
            $crawler->selector_ul_li = 'x';
            $crawler->selector_ul_li_key = 'x';
            $crawler->selector_ul_li_val = 'x';
            $crawler->selector_seller_name = 'x';
            $crawler->selector_selles_phones = 'x';
            $crawler->save();

            return redirect()->route('crawlers.shopping.bot', $crawler->id);
        }

        return view('crawlers.shopping.view', compact('crawler'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin create
    # 
    public static function update(UpdateRequest $request)
    {

    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin status
    # 
    public static function status(StatusRequest $request)
    {
        $crawler = ShoppingCrawler::where('id', $request->id)->first();

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
