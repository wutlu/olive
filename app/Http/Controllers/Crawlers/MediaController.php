<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\Crawlers\Media\StatusRequest;
use App\Http\Requests\Crawlers\Media\UpdateRequest;
use App\Http\Requests\Crawlers\Media\DeleteRequest;

use App\Models\Crawlers\MediaCrawler;

use App\Jobs\Elasticsearch\CreateMediaIndexJob;
use App\Jobs\Elasticsearch\DeleteIndexJob;

use App\Utilities\Crawler;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

class MediaController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # media botları view
    # 
    public static function listView()
    {
        return view('crawlers.media.list');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # medya botları json çıktısı.
    # 
    public static function listViewJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new MediaCrawler;
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
    # tüm istatistikler.
    # 
    public static function allStatistics()
    {
        $media_crawler = new MediaCrawler;
        $document = new Document;

        return [
            'status' => 'ok',
            'data' => [
                'count' => [
                    'active' => $media_crawler->where('status', true)->count(),
                    'disabled' => $media_crawler->where('status', false)->count(),
                    'buffer' => $document->count([ 'articles', '*' ], 'article', [
                        'query' => [
                            'match' => [
                                'status' => 'buffer'
                            ]
                        ]
                    ]),
                    'success' => $document->count([ 'articles', '*' ], 'article', [
                        'query' => [
                            'match' => [
                                'status' => 'ok'
                            ]
                        ]
                    ]),
                    'failed' => $document->count([ 'articles', '*' ], 'article', [
                        'query' => [
                            'match' => [
                                'status' => 'failed'
                            ]
                        ]
                    ])
                ],
                'elasticsearch' => Indices::stats([ 'articles', '*' ])
            ]
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # çalışmayan tüm botları başlat.
    # 
    public static function allStart()
    {
        $crawlers = MediaCrawler::where([
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
    # çalışan tüm botları durdur.
    # 
    public static function allStop()
    {
        $crawlers = MediaCrawler::where('status', true)->update([ 'status' => false ]);

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # tüm eksik indexleri oluştur.
    # 
    public static function allIndex()
    {
        $crawlers = MediaCrawler::where('elasticsearch_index', false)->where('test', true)->get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                CreateMediaIndexJob::dispatch($crawler->id)->onQueue('elasticsearch');
            }
        }

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # bot sil.
    # 
    public static function delete(DeleteRequest $request)
    {
        $crawler = MediaCrawler::where('id', $request->id)->delete();

        DeleteIndexJob::dispatch([ 'articles', $request->id ])->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # index istatistikleri.
    # 
    public static function statistics(int $id)
    {
        $crawler = MediaCrawler::where('id', $id)->firstOrFail();
        $document = new Document;

        return [
            'status' => 'ok',
            'data' => [
                'crawler' => $crawler,
                'count' => [
                    'buffer' => $document->count([ 'articles', $crawler->id ], 'article', [
                        'query' => [
                            'match' => [
                                'status' => 'buffer'
                            ]
                        ]
                    ]),
                    'success' => $document->count([ 'articles', $crawler->id ], 'article', [
                        'query' => [
                            'match' => [
                                'status' => 'ok'
                            ]
                        ]
                    ]),
                    'failed' => $document->count([ 'articles', $crawler->id ], 'article', [
                        'query' => [
                            'match' => [
                                'status' => 'failed'
                            ]
                        ]
                    ])
                ],
                'elasticsearch' => Indices::stats([ 'articles', $crawler->id ])
            ]
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # bot view.
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
            $crawler->name = 'Yeni Bot '.rand(99999, 999999);
            $crawler->site = 'http://';
            $crawler->url_pattern = '([a-z0-9-]{4,128})';
            $crawler->selector_title = 'h1';
            $crawler->selector_description = 'h2';
            $crawler->save();

            return redirect()->route('crawlers.media.bot', $crawler->id);
        }

        return view('crawlers.media.view', compact('crawler'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # bot oluştur.
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

            if ($accepted > $total/3)
            {
                $crawler->fill($request->all());
                $crawler->test = true;
                $crawler->error_count = 0;
                $crawler->off_reason = null;

                $data['status'] = 'ok';

                CreateMediaIndexJob::dispatch($crawler->id)->onQueue('elasticsearch');
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
    # bot durumu.
    # 
    public static function status(StatusRequest $request)
    {
        $crawler = MediaCrawler::where('id', $request->id)->first();

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
