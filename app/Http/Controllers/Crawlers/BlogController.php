<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\Crawlers\Blog\ListRequest;
use App\Http\Requests\Crawlers\Blog\StatusRequest;
use App\Http\Requests\Crawlers\Blog\UpdateRequest;
use App\Http\Requests\Crawlers\Blog\DeleteRequest;

use App\Models\Crawlers\BlogCrawler;

use App\Utilities\Crawler;
use App\Utilities\Term;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

use App\Models\Option;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Jobs\Elasticsearch\DeleteDocumentJob;

class BlogController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Blog Botları, durum yönetimi ana sayfası.
     *
     * @return view
     */
    public static function listView()
    {
        return view('crawlers.blog.list');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Blog Modülü, bot listesi.
     *
     * @return array
     */
    public static function listViewJson(ListRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new BlogCrawler;
        $query = $request->string ? $query->where(function($query) use ($request) {
            $query->orWhere('name', 'ILIKE', '%'.$request->string.'%');
            $query->orWhere('site', 'ILIKE', '%'.$request->string.'%');
        }) : $query;

        if ($request->status)
        {
            $query = $query->where('status', $request->status == 'on' ? true : false);
        }

        $query = $query->skip($skip)->take($take);

        if ($request->sort)
        {
            switch ($request->sort)
            {
                case 'alexa-down':
                    $query = $query->orderBy('alexa_rank', 'DESC');
                break;
                case 'alexa-up':
                    $query = $query->orderBy('alexa_rank', 'ASC');
                break;
                case 'hit-down':
                    $query = $query->orderBy('count', 'DESC');
                break;
                case 'hit-up':
                    $query = $query->orderBy('count', 'ASC');
                break;
                case 'interval':
                    $query = $query->orderBy('control_interval', 'ASC');
                break;
                case 'error':
                    $query = $query->orderBy('error_count', 'DESC');
                break;
            }
        }
        else
        {
            $query = $query->orderBy('status', 'ASC');
            $query = $query->orderBy('error_count', 'DESC');
            $query = $query->orderBy('control_interval', 'ASC');
        }

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
     * Blog Modülü, Elasticsearch index istatistikleri.
     *
     * @return array
     */
    public static function allStatistics()
    {
        $blog_crawler = new BlogCrawler;
        $document = new Document;

        return [
            'status' => 'ok',
            'data' => [
                'count' => [
                    'active' => $blog_crawler->where('status', true)->count(),
                    'disabled' => $blog_crawler->where('status', false)->count(),
                    'buffer' => $document->count([ 'blog', '*' ], 'document', [
                        'query' => [
                            'match' => [
                                'status' => 'buffer'
                            ]
                        ]
                    ]),
                    'success' => $document->count([ 'blog', '*' ], 'document', [
                        'query' => [
                            'match' => [
                                'status' => 'ok'
                            ]
                        ]
                    ]),
                    'failed' => $document->count([ 'blog', '*' ], 'document', [
                        'query' => [
                            'match' => [
                                'status' => 'failed'
                            ]
                        ]
                    ])
                ],
                'elasticsearch' => Indices::stats([ 'blog', '*' ])
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Blog Modülü, çalışmayan botları başlatma tetikleyicisi.
     *
     * @return array
     */
    public static function allStart()
    {
        $status = Option::where('key', 'blog.index.status')->value('value');

        if (count(config('database.elasticsearch.blog.groups')) == $status)
        {
            $crawlers = BlogCrawler::where(
                [
                    'status' => false,
                    'test' => true
                ]
            )->update(
                [
                    'status' => true
                ]
            );
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
     * Blog Modülü, çalışan botları durdurma tetikleyicisi.
     *
     * @return array
     */
    public static function allStop()
    {
        $crawlers = BlogCrawler::where('status', true)->update([ 'status' => false ]);

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Blog Modülü, başarısız içerikleri silme tetikleyicisi.
     *
     * @return array
     */
    public static function allClear()
    {
        DeleteDocumentJob::dispatch([ 'blog', 's*' ], 'document', [
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
     * Blog Modülü, başarısız içerikleri silme tetikleyicisi.
     *
     * @return array
     */
    public static function clear(int $id)
    {
        $bot = BlogCrawler::where('id', $id)->firstOrFail();

        DeleteDocumentJob::dispatch([ 'blog', $bot->elasticsearch_index_name ], 'document', [
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
     * Blog Modülü, bot silme tetikleyicisi.
     *
     * @return array
     */
    public static function delete(DeleteRequest $request)
    {
        $crawler = BlogCrawler::where('id', $request->id)->firstOrFail();

        Document::deleteByQuery(
            [
                'blog',
                $crawler->elasticsearch_index_name
            ],
            'document',
            [
                'query' => [
                    'bool' => [
                        'must' => [
                            [ 'match' => [ 'site_id' => $crawler->id ] ]
                        ]
                    ]
                ]
            ]
        );

        $crawler->delete();

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Blog Modülü, Elasticsearch index istatistikleri.
     *
     * @return array
     */
    public static function statistics(int $id)
    {
        $crawler = BlogCrawler::where('id', $id)->firstOrFail();

        $document = new Document;

        return [
            'status' => 'ok',
            'data' => [
                'crawler' => $crawler,
                'count' => [
                    'buffer' => $document->count([ 'blog', $crawler->elasticsearch_index_name ], 'document', [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'status' => 'buffer' ] ],
                                    [ 'match' => [ 'site_id' => $crawler->id ] ]
                                ]
                            ]
                        ],
                    ]),
                    'success' => $document->count([ 'blog', $crawler->elasticsearch_index_name ], 'document', [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'status' => 'ok' ] ],
                                    [ 'match' => [ 'site_id' => $crawler->id ] ]
                                ]
                            ]
                        ]
                    ]),
                    'failed' => $document->count([ 'blog', $crawler->elasticsearch_index_name ], 'document', [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'status' => 'failed' ] ],
                                    [ 'match' => [ 'site_id' => $crawler->id ] ]
                                ]
                            ]
                        ]
                    ])
                ]
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Blog Modülü, bot detayları sayfası.
     *
     * @return view
     */
    public static function view(int $id = 0)
    {
        if ($id)
        {
            $crawler = BlogCrawler::where('id', $id)->firstOrFail();
        }
        else
        {
            $crawler = new BlogCrawler;
            $crawler->name = 'Yeni Bot '.rand(99999, 999999);
            $crawler->site = 'http://';
            $crawler->url_pattern = '([a-z0-9-]{4,128})';
            $crawler->selector_title = 'h1';
            $crawler->selector_description = 'h2';
            $crawler->elasticsearch_index_name = self::getBestIndex();
            $crawler->off_limit = 20;
            $crawler->proxy = true;
            $crawler->save();

            return redirect()->route('crawlers.blog.bot', $crawler->id);
        }

        return view('crawlers.blog.view', compact('crawler'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * En az içeriğe sahip olan indexin seçilimi.
     *
     * @return string
     */
    public static function getBestIndex()
    {
        $counts = [];

        foreach (config('database.elasticsearch.blog.groups') as $group)
        {
            $counts[$group] = BlogCrawler::where('elasticsearch_index_name', $group)->count();
        }

        $sorted = array_sort($counts);

        return array_keys($sorted)[0];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Blog Modülü, bot oluştur veya bot güncelle.
     *
     * @return array
     */
    public static function update(UpdateRequest $request)
    {
        $crawler = BlogCrawler::where('id', $request->id)->first();

        $data['status'] = 'err';

        $links = Crawler::articleLinkDetection(
            $request->site,
            $request->url_pattern ? $request->url_pattern : null,
            $request->base,
            $request->standard ? true : false,
            $request->proxy ? true : false
        );

        $total = 0;
        $accepted = 0;

        if (@$links->links)
        {
            foreach ($links->links as $link)
            {
                if ($total < $request->test_count)
                {
                    $item = Crawler::articleDetection(
                        $request->site,
                        $link,
                        $request->selector_title ? $request->selector_title : null,
                        $request->selector_description ? $request->selector_description : null,
                        $request->standard ? true : false,
                        $request->proxy ? true : false
                    );

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
                $crawler->standard = $request->standard ? true : false;
                $crawler->test = true;
                $crawler->error_count = 0;
                $crawler->off_reason = null;

                $data['status'] = 'ok';
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
     * Blog Modülü, bot durumu değiştirme.
     * - Çalışan botu durdur.
     * - Durmuş botu çalıştır.
     *
     * @return array
     */
    public static function status(StatusRequest $request)
    {
        $crawler = BlogCrawler::where('id', $request->id)->first();

        $crawler->status = $crawler->status ? 0 : 1;
        $crawler->save();

        return [
            'status' => 'ok',
            'data' => [
                'status' => $crawler->status
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Blog, index listesi.
     *
     * @return view
     */
    public static function indices()
    {
        $rows = Option::whereIn('key', [
            'blog.index.status'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        $index_groups = config('database.elasticsearch.blog.groups');

        return view('crawlers.blog.indices', compact('options', 'index_groups'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Blog, index listesi.
     *
     * @return array
     */
    public static function indicesJson()
    {
        $client = new Client([
            'base_uri' => array_random(config('database.connections.elasticsearch.node.ips')),
            'handler' => HandlerStack::create()
        ]);

        $source = $client->get('/_cat/indices/'.config('system.db.alias').'__blog*?format=json&s=index:desc')->getBody();
        $source = json_decode($source);

        return [
            'status' => 'ok',
            'hits' => $source
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Blog, Elasticsearch index oluşturucu.
     *
     * @return array
     */
    public static function indexCreate()
    {
        $count = 0;
        $groups = config('database.elasticsearch.blog.groups');

        $es = new BlogCrawler;

        foreach ($groups as $group)
        {
            $indices = $es->indexCreate($group);

            if ($indices->status == 'created' || $indices->status == 'exists')
            {
                $count++;
            }
        }

        Option::updateOrCreate(
            [
                'key' => 'blog.index.status'
            ],
            [
                'value' => $count
            ]
        );

        return [
            'status' => ($count == count($groups)) ? 'ok' : 'err'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ****** SYSTEM ******
     ********************
     *
     * Blog Counter
     *
     * @return mixed
     */
    public static function counter()
    {
        $crawlers = BlogCrawler::get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                echo Term::line($crawler->id.' - '.$crawler->name);
                echo Term::line($crawler->count);

                $document = Document::count(
                    [
                        'blog',
                        $crawler->elasticsearch_index_name
                    ],
                    'document',
                    [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'site_id' => $crawler->id ] ]
                                ]
                            ]
                        ]
                    ]
                );

                $crawler->update([ 'count' => $document->data['count'] ]);

                echo Term::line($document->data['count']);

                echo '----';
                echo PHP_EOL;
            }
        }
    }
}
