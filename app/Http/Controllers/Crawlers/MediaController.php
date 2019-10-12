<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\Crawlers\Media\ListRequest;
use App\Http\Requests\Crawlers\Media\StatusRequest;
use App\Http\Requests\Crawlers\Media\UpdateRequest;
use App\Http\Requests\Crawlers\Media\DeleteRequest;

use App\Models\Crawlers\MediaCrawler;

use App\Utilities\Crawler;
use App\Utilities\Term;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

use App\Models\Option;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Jobs\Elasticsearch\DeleteDocumentJob;

use App\Models\Geo\States;

class MediaController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Medya Botları, durum yönetimi ana sayfası.
     *
     * @return view
     */
    public static function listView()
    {
        return view('crawlers.media.list');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Medya Modülü, bot listesi.
     *
     * @return array
     */
    public static function listViewJson(ListRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new MediaCrawler;
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
     * Medya Modülü, Elasticsearch index istatistikleri.
     *
     * @return array
     */
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
                    'buffer' => $document->count([ 'media', '*' ], 'article', [
                        'query' => [
                            'match' => [
                                'status' => 'buffer'
                            ]
                        ]
                    ]),
                    'success' => $document->count([ 'media', '*' ], 'article', [
                        'query' => [
                            'match' => [
                                'status' => 'ok'
                            ]
                        ]
                    ]),
                    'failed' => $document->count([ 'media', '*' ], 'article', [
                        'query' => [
                            'match' => [
                                'status' => 'failed'
                            ]
                        ]
                    ])
                ],
                'elasticsearch' => Indices::stats([ 'media', '*' ])
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Medya Modülü, çalışmayan botları başlatma tetikleyicisi.
     *
     * @return array
     */
    public static function allStart()
    {
        $status = Option::where('key', 'media.index.status')->value('value');

        if (count(config('database.elasticsearch.media.groups')) == $status)
        {
            $crawlers = MediaCrawler::where(
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
     * Medya Modülü, çalışan botları durdurma tetikleyicisi.
     *
     * @return array
     */
    public static function allStop()
    {
        $crawlers = MediaCrawler::where('status', true)->update([ 'status' => false ]);

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Medya Modülü, başarısız içerikleri silme tetikleyicisi.
     *
     * @return array
     */
    public static function allClear()
    {
        DeleteDocumentJob::dispatch([ 'media', 's*' ], 'article', [
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
     * Medya Modülü, başarısız içerikleri silme tetikleyicisi.
     *
     * @return array
     */
    public static function clear(int $id)
    {
        $bot = MediaCrawler::where('id', $id)->firstOrFail();

        DeleteDocumentJob::dispatch([ 'media', $bot->elasticsearch_index_name ], 'article', [
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
     * Medya Modülü, bot silme tetikleyicisi.
     *
     * @return array
     */
    public static function delete(DeleteRequest $request)
    {
        $crawler = MediaCrawler::where('id', $request->id)->firstOrFail();

        Document::deleteByQuery(
            [
                'media',
                $crawler->elasticsearch_index_name
            ],
            'article',
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
     * Medya Modülü, Elasticsearch index istatistikleri.
     *
     * @return array
     */
    public static function statistics(int $id)
    {
        $crawler = MediaCrawler::where('id', $id)->firstOrFail();

        $document = new Document;

        return [
            'status' => 'ok',
            'data' => [
                'crawler' => $crawler,
                'count' => [
                    'buffer' => $document->count([ 'media', $crawler->elasticsearch_index_name ], 'article', [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'status' => 'buffer' ] ],
                                    [ 'match' => [ 'site_id' => $crawler->id ] ]
                                ]
                            ]
                        ],
                    ]),
                    'success' => $document->count([ 'media', $crawler->elasticsearch_index_name ], 'article', [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'status' => 'ok' ] ],
                                    [ 'match' => [ 'site_id' => $crawler->id ] ]
                                ]
                            ]
                        ]
                    ]),
                    'failed' => $document->count([ 'media', $crawler->elasticsearch_index_name ], 'article', [
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
     * Medya Modülü, bot detayları sayfası.
     *
     * @return view
     */
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
            $crawler->elasticsearch_index_name = self::getBestIndex();
            $crawler->off_limit = 20;
            $crawler->proxy = true;
            $crawler->save();

            return redirect()->route('crawlers.media.bot', $crawler->id);
        }

        $states = States::where('country_id', 223)->orderBy('name', 'ASC')->get();

        return view('crawlers.media.view', compact('crawler', 'states'));
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

        foreach (config('database.elasticsearch.media.groups') as $group)
        {
            $counts[$group] = MediaCrawler::where('elasticsearch_index_name', $group)->count();
        }

        $sorted = array_sort($counts);

        return array_keys($sorted)[0];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Medya Modülü, bot oluştur veya bot güncelle.
     *
     * @return array
     */
    public static function update(UpdateRequest $request)
    {
        $crawler = MediaCrawler::where('id', $request->id)->first();

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
     * Medya Modülü, bot durumu değiştirme.
     * - Çalışan botu durdur.
     * - Durmuş botu çalıştır.
     *
     * @return array
     */
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

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Medya, index listesi.
     *
     * @return view
     */
    public static function indices()
    {
        $rows = Option::whereIn('key', [
            'media.index.status'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        $index_groups = config('database.elasticsearch.media.groups');

        return view('crawlers.media.indices', compact('options', 'index_groups'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Medya, index listesi.
     *
     * @return array
     */
    public static function indicesJson()
    {
        $client = new Client([
            'base_uri' => array_random(config('database.connections.elasticsearch.node.ips')),
            'handler' => HandlerStack::create()
        ]);

        $source = $client->get('/_cat/indices/'.config('system.db.alias').'__media*?format=json&s=index:desc')->getBody();
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
     * Medya, Elasticsearch index oluşturucu.
     *
     * @return array
     */
    public static function indexCreate()
    {
        $count = 0;
        $groups = config('database.elasticsearch.media.groups');

        $es = new MediaCrawler;

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
                'key' => 'media.index.status'
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
     * Media Counter
     *
     * @return mixed
     */
    public static function counter()
    {
        $crawlers = MediaCrawler::get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                echo Term::line($crawler->id.' - '.$crawler->name);
                echo Term::line($crawler->count);

                $document = Document::count(
                    [
                        'media',
                        $crawler->elasticsearch_index_name
                    ],
                    'article',
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
