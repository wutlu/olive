<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Elasticsearch\Document;

use Term;

use App\Http\Requests\ReplicaRequest;

use App\Models\Crawlers\MediaCrawler;

class ReplicaController extends Controller
{
    public function __construct()
    {
        ### [ üyelik zorunlu ] ###
        $this->middleware([ 'auth' ]);

        ### [ 100 işlemden sonra 5 dakika ile sınırla ] ###
        $this->middleware('throttle:100,5')->only([
        ]);
    }

    /**
     * Replica Tespiti
     *
     * @return view
     */
    public static function dashboard()
    {
        $elements = [
            'start_date',
            'end_date',
            'string',
            'source',
            'smilarity'
        ];

        $elements = implode(',', $elements);

        return view('replica', compact('elements'));
    }

    /**
     * Replica Tespiti Sorgu
     *
     * @return array
     */
    public static function search(ReplicaRequest $request)
    {
        $starttime = explode(' ', microtime());
        $starttime = $starttime[1] + $starttime[0];

        $smilar = Term::commonWords($request->string);
        $clean = Term::cleanSearchQuery($request->string);

        $crawler_count = MediaCrawler::count();

        $q = [
            'from' => $request->skip,
            'size' => $request->take,
            'sort' => [ 'created_at' => 'desc' ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd',
                                    'gte' => $request->start_date,
                                    'lte' => $request->end_date
                                ]
                            ]
                        ]
                    ],
                    'must' => [
                        [ 'exists' => [ 'field' => 'created_at' ] ],
                        [
                            'more_like_this' => [
                                'fields' => [ 'title', 'description' ],
                                'like' => array_keys($smilar),
                                'min_term_freq' => 1,
                                'min_doc_freq' =>1,
                                'min_word_length' =>1
                            ]
                        ]
                    ]
                ]
            ],
            'aggs' => [
                'locals' => [
                    'terms' => [
                        'field' => 'state',
                        'size' => 100
                    ]
                ],
                'unique' => [
                    'terms' => [
                        'field' => 'site_id',
                        'size' => $crawler_count
                    ]
                ]
            ],
            'min_score' => $request->smilarity / 10,
            'from' => $request->skip,
            'size' => $request->take,
        ];

        $query = Document::search([ 'media', '*' ], 'article', $q);

        $results = [
            'status' => $query->status,
            'aggs' => [],
            'stats' => [
                'hits' => 0,
                'took' => 0
            ],
            'words' => $clean->words
        ];

        if ($query->status == 'ok')
        {
            $media_crawlers = null;

            if (@$query->data['aggregations']['unique']['buckets'])
            {
                $ids = array_map(function($item) {
                    return $item['key'];
                }, $query->data['aggregations']['unique']['buckets']);

                $media_crawlers = @MediaCrawler::select([ 'id', 'name', 'site', 'base' ])->whereIn('id', $ids)->get()->keyBy('id')->toArray();

                if ($media_crawlers)
                {
                    $results['aggs']['unique'] = array_map(function($bucket) use($media_crawlers) {
                        return [
                            'name' => $media_crawlers[$bucket['key']]['name'],
                            'address' => $media_crawlers[$bucket['key']]['site'],
                            'base' => $media_crawlers[$bucket['key']]['base'],
                            'hit' => $bucket['doc_count']
                        ];
                    }, $query->data['aggregations']['unique']['buckets']);
                }
            }

            $results['hits'] = array_map(function($item) use($media_crawlers) {
                $line = $item['_source'];
                $site = @$media_crawlers[$item['_source']['site_id']];

                if ($site)
                {
                    unset($line['site_id']);

                    $line['created_at'] = date('d.m.Y H:i', strtotime($line['created_at']));

                    $line['site']['name'] = $site['name'];
                    $line['site']['address'] = $site['site'];

                    if ($site['base'] != '/')
                    {
                        $line['site']['address'] = $site['site'].'/'.$site['base'];
                    }
                }

                return $line;
            }, $query->data['hits']['hits']);

            if (@$query->data['aggregations']['locals']['buckets'])
            {
                $results['aggs']['locals'] = $query->data['aggregations']['locals']['buckets'];
            }

            $results['stats']['hits'] = $query->data['hits']['total'];
        }

        $mtime = explode(' ', microtime());
        $totaltime = $mtime[0] + $mtime[1] - $starttime;

        if ($results['stats']['hits'])
        {
            $results['stats']['took'] = sprintf('%0.2f', $totaltime);
        }

        return $results;
    }
}
