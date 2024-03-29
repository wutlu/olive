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

use App\Jobs\Elasticsearch\DeleteDocumentJob;

class ShoppingController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Botları, durum yönetimi ana sayfası.
     *
     * @return view
     */
    public static function listView()
    {
        return view('crawlers.shopping.list');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, bot listesi.
     *
     * @return array
     */
    public static function listViewJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new ShoppingCrawler;
        $query = $request->string ? $query->orWhere('name', 'ILIKE', '%'.$request->string.'%')
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

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, Elasticsearch index istatistikleri.
     *
     * @return array
     */
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

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, çalışmayan botları başlatma tetikleyicisi.
     *
     * @return array
     */
    public static function allStart()
    {
        $crawlers = ShoppingCrawler::where(
            [
                'status' => false,
                'elasticsearch_index' => true,
                'test' => true
            ]
        )->update(
            [
                'status' => true
            ]
        );

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, çalışan botları durdurma tetikleyicisi.
     *
     * @return array
     */
    public static function allStop()
    {
        $crawlers = ShoppingCrawler::where('status', true)->update([ 'status' => false ]);

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, başarısız içerikleri silme tetikleyicisi.
     *
     * @return array
     */
    public static function allClear()
    {
        DeleteDocumentJob::dispatch([ 'shopping', '*' ], 'product', [
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'match' => [ 'status' => 'failed' ] ]
                    ]
                ]
            ]
        ])->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, başarısız içerikleri silme tetikleyicisi.
     *
     * @return array
     */
    public static function clear(int $id)
    {
        $bot = ShoppingCrawler::where('id', $id)->firstOrFail();

        DeleteDocumentJob::dispatch([ 'shopping', $bot->id ], 'product', [
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'match' => [ 'status' => 'failed' ] ],
                        [ 'match' => [ 'site_id' => $bot->id ] ]
                    ]
                ]
            ]
        ])->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, Elasticsearch, eksik tüm indexleri oluşturma tetikleyicisi.
     * - Indexlerin tetiklenmesi için botların test edilmiş olması gerekir.
     *
     * @return array
     */
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

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, bot silme tetikleyicisi.
     *
     * @return array
     */
    public static function delete(DeleteRequest $request)
    {
        $crawler = ShoppingCrawler::where('id', $request->id)->delete();

        DeleteIndexJob::dispatch([ 'shopping', $request->id ])->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, Elasticsearch index istatistikleri.
     *
     * @return array
     */
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

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, bot detayları sayfası.
     *
     * @return view
     */
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
            $crawler->site = 'https://';
            $crawler->url_pattern = '([a-z0-9-]{4,128})';
            $crawler->selector_title = 'h1';
            $crawler->selector_description = '#classifiedDescription';
            $crawler->selector_seller_name = '.username-info-area';
            $crawler->selector_seller_phones = '.pretty-phone-part';
            $crawler->selector_price = '.classifiedInfo > h3';
            $crawler->save();

            return redirect()->route('crawlers.shopping.bot', $crawler->id);
        }

        return view('crawlers.shopping.view', compact('crawler'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, bot oluştur veya bot güncelle.
     *
     * @return array
     */
    public static function update(UpdateRequest $request)
    {
        $crawler = ShoppingCrawler::where('id', $request->id)->first();

        $data['status'] = 'err';

        $links = Crawler::googleSearchResultLinkDetection(
            $request->site,
            $request->url_pattern,
            $request->google_search_query,
            $request->google_time,
            $request->google_max_page
        );

        $total = 0;
        $accepted = 0;

        if (@$links->links)
        {
            foreach ($links->links as $link)
            {
                if ($total < $request->test_count)
                {
                    $item = Crawler::productDetection($request->site, $link, [
                        'title'         => $request->selector_title,
                        'description'   => $request->selector_description,
                        'seller_name'   => $request->selector_seller_name,
                        'seller_phones' => $request->selector_seller_phones,
                        'price'         => $request->selector_price,
                    ]);

                    $data['items'][] = $item;

                    if ($item->status == 'ok')
                    {
                        $accepted++;
                    }

                    $total++;
                }
            }

            if ($accepted > $total/3)
            {
                $crawler->fill($request->all());
                $crawler->proxy = $request->proxy ? true : false;
                $crawler->test = true;
                $crawler->error_count = 0;
                $crawler->off_reason = null;

                $data['status'] = 'ok';

                CreateShoppingIndexJob::dispatch($crawler->id)->onQueue('elasticsearch');
            }

            $crawler->save();
        }
        else
        {
            $data['error_reasons'] = $links->error_reasons;
        }

        return $data;
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Alışveriş Modülü, bot durumu değiştirme.
     * - Çalışan botu durdur.
     * - Durmuş botu çalıştır.
     *
     * @return array
     */
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
