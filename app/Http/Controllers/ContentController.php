<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Elasticsearch\Document;

use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\ShoppingCrawler;

use Carbon\Carbon;

use App\Utilities\Term;

use App\Http\Requests\SearchRequest;

class ContentController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth');

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyonu Olanlar
         */
        $this->middleware('organisation:have');

        /**
         ***** ZORUNLU *****
         *
         * - Organizasyon Onayı
         */
        $this->middleware('can:organisation-status');
    }

    /**
     * Modül Ayraç
     *
     * @return view
     */
    public static function module(string $es_index, string $es_type, string $es_id)
    {
        $document = Document::get($es_index, $es_type, $es_id);

        if ($document->status == 'ok')
        {
            $data = [];
            $document = $document->data;

            switch ($es_type)
            {
                case 'entry':
                    $crawler = SozlukCrawler::where('id', $document['_source']['site_id'])->firstOrFail();

                    $site = [
                        [ 'match' => [ 'group_name' => $document['_source']['group_name'] ] ]
                    ];

                    $data = [
                        'total' => Document::count($es_index, 'entry', [
                            'query' => [
                                'bool' => [
                                    'must' => $site
                                ]
                            ]
                        ]),
                        'pos' => Document::count($es_index, 'entry', [
                            'query' => [
                                'bool' => [
                                    'must' => $site,
                                    'filter' => [
                                        [ 'range' => [ 'sentiment.pos' => [ 'gte' => .34 ] ] ]
                                    ]
                                ]
                            ]
                        ]),
                        'neg' => Document::count($es_index, 'entry', [
                            'query' => [
                                'bool' => [
                                    'must' => $site,
                                    'filter' => [
                                        [ 'range' => [ 'sentiment.neg' => [ 'gte' => .34 ] ] ]
                                    ]
                                ]
                            ]
                        ]),
                        'popular' => Document::list($es_index, 'entry', [
                            'size' => 0,
                            'query' => [
                                'bool' => [
                                    'must' => $site
                                ]
                            ],
                            'aggs' => [
                                'popular_keywords' => [
                                    'terms' => [
                                        'field' => 'entry',
                                        'size' => 100
                                    ]
                                ]
                            ]
                        ])
                    ];

                    $bucket = @$data['popular']->data['aggregations']['popular_keywords']['buckets'];

                    if ($bucket)
                    {
                        $bucket = implode(' ', array_map(function($a) {
                            return $a['key'];
                        }, $bucket));

                        $data['keywords'] = Term::commonWords($bucket, 100);
                    }

                    $title = implode(' ', [ $crawler->name, '/', $document['_source']['title'] ]);
                break;

                case 'product':
                    $crawler = ShoppingCrawler::where('id', $document['_source']['site_id'])->firstOrFail();

                    $site = [
                        [ 'match' => [ 'site_id' => $crawler->id ] ],
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ];

                    $title = implode(' ', [ $crawler->name, '/', $document['_source']['title'] ]);
                break;

                case 'tweet':
                    $title = implode(' / ', [ 'Twitter', $document['_source']['user']['name'], '#'.$es_id ]);

                    $user = [
                        [ 'match' => [ 'user.id' => $document['_source']['user']['id'] ] ]
                    ];

                    $data = [
                        'total' => Document::count([ 'twitter', 'tweets', '*' ], 'tweet', [
                            'query' => [
                                'bool' => [
                                    'must' => $user
                                ]
                            ]
                        ])
                    ];
                break;

                case 'video':
                    $title = implode(' / ', [ 'YouTube', $document['_source']['title'], '#'.$es_id ]);
                break;

                case 'comment':
                    $title = implode(' / ', [ 'YouTube', $document['_source']['channel']['title'], '#'.$es_id ]);
                break;

                case 'article':
                    $crawler = MediaCrawler::where('id', $document['_source']['site_id'])->firstOrFail();

                    $site = [
                        [ 'match' => [ 'site_id' => $crawler->id ] ],
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ];

                    $data = [
                        'total' => Document::count($es_index, 'article', [
                            'query' => [
                                'bool' => [
                                    'must' => $site
                                ]
                            ]
                        ]),
                        'pos' => Document::count($es_index, 'article', [
                            'query' => [
                                'bool' => [
                                    'must' => $site,
                                    'filter' => [
                                        [ 'range' => [ 'sentiment.pos' => [ 'gte' => .34 ] ] ]
                                    ]
                                ]
                            ]
                        ]),
                        'neg' => Document::count($es_index, 'article', [
                            'query' => [
                                'bool' => [
                                    'must' => $site,
                                    'filter' => [
                                        [ 'range' => [ 'sentiment.neg' => [ 'gte' => .34 ] ] ]
                                    ]
                                ]
                            ]
                        ]),
                        'popular' => Document::list($es_index, 'article', [
                            'size' => 0,
                            'query' => [
                                'bool' => [
                                    'must' => $site
                                ]
                            ],
                            'aggs' => [
                                'popular_keywords' => [
                                    'terms' => [
                                        'field' => 'description',
                                        'size' => 100
                                    ]
                                ]
                            ]
                        ])
                    ];

                    $bucket = @$data['popular']->data['aggregations']['popular_keywords']['buckets'];

                    if ($bucket)
                    {
                        $bucket = implode(' ', array_map(function($a) {
                            return $a['key'];
                        }, $bucket));

                        $data['keywords'] = Term::commonWords($bucket, 100);
                    }

                    $title = implode(' / ', [ $crawler->name, '/', $document['_source']['title'] ]);
                break;

                default: abort(404); break;
            }

            $es = (object) [
                'index' => $es_index,
                'type' => $es_type,
                'id' => $es_id
            ];

            return view(implode('.', [ 'content', $es_type ]), compact('document', 'title', 'es', 'data'));
        }

        return abort(404);
    }

    /**
     * Histogram
     *
     * @return array
     */
    public static function histogram(string $type, string $es_index, string $es_type, string $es_id)
    {
        switch ($type)
        {
            case 'hourly':
                $script = 'doc.created_at.value.getHourOfDay()';
                $max = 23;
            break;
            case 'weekly':
                $script = 'doc.created_at.value.getDayOfWeek()';
                $max = 6;
            break;
        }

        $arr = [
                'size' => 0,
                'query' => [
                    'bool' => [
                        'must' => [
                            [ 'exists' => [ 'field' => 'created_at' ] ]
                        ]
                    ]
                ],
                'aggs' => [
                    'results' => [
                        'histogram' => [
                            'script' => $script,
                            'interval' => 1,
                            'min_doc_count' => 0,
                            'extended_bounds' => [
                                'min' => 1,
                                'max' => $max
                            ]
                        ]
                    ]
                ]
        ];

        switch ($es_type)
        {
            case 'entry':
                $arr['query']['bool']['must'][] = [
                    'match' => [ 'group_name' => $es_id ]
                ];

                $document = Document::list($es_index, $es_type, $arr);
            break;
            case 'article':
                $arr['query']['bool']['must'][] = [
                    'match' => [ 'site_id' => $es_id ]
                ];

                $document = Document::list($es_index, $es_type, $arr);
            break;
            case 'product':
                $doc = Document::get($es_index, $es_type, $es_id);

                if ($doc->status != 'ok')
                {
                    return [
                        'status' => 'err',
                        'data' => [
                            'reason' => 'not found'
                        ]
                    ];
                }

                $smilar = Term::commonWords($doc->data['_source']['title']);

                if (count($smilar))
                {
                    $arr['query']['bool']['must'][] = [
                        'more_like_this' => [
                            'fields' => [ 'title', 'description' ],
                            'like' => array_keys($smilar),
                            'min_term_freq' => 1,
                            'min_doc_freq' => 1
                        ]
                    ];
                }

                $document = Document::list($es_index, $es_type, $arr);
            break;
            case 'tweet':
                $doc = Document::get($es_index, $es_type, $es_id);

                if ($doc->status != 'ok')
                {
                    return [
                        'status' => 'err',
                        'data' => [
                            'reason' => 'not found'
                        ]
                    ];
                }

                $arr['query']['bool']['must'][] = [
                    'match' => [
                        'user.id' => $doc->data['_source']['user']['id']
                    ]
                ];

                $document = Document::list([ 'twitter', 'tweets', '*' ], $es_type, $arr);
            break;
        }

        //$document = json_encode(json_decode($document->message), JSON_PRETTY_PRINT); print_r($document); exit();

        return [
            'status' => $document->status,
            'data' => $document->data
        ];
    }

    /**
     * Benzer İçerikler
     *
     * @return array
     */
    public static function smilar(string $es_index, string $es_type, string $es_id, SearchRequest $request)
    {
        $document = Document::get($es_index, $es_type, $es_id);

        if ($document->status == 'ok')
        {
            if ($es_type == 'tweet')
            {
                $documents = Document::list([ 'twitter', 'tweets', '*' ], $es_type, [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'match' => [ 'user.id' => $document->data['_source']['user']['id'] ]
                                ]
                            ]
                        ]
                    ],
                    'from' => $request->skip,
                    'size' => $request->take,
                    //'_source' => [ 'url', 'title', 'description', 'created_at' ]
                ]);
            }
            else
            {
                $smilar = Term::commonWords($document->data['_source']['title']);

                if ($smilar)
                {
                    switch ($es_type)
                    {
                        case 'article':
                            $documents = Document::list([ 'media', '*' ], $es_type, [
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'match' => [ 'status' => 'ok' ]
                                            ],
                                            [
                                                'more_like_this' => [
                                                    'fields' => [ 'title' ],
                                                    'like' => array_keys($smilar),
                                                    'min_term_freq' => 1,
                                                    'min_doc_freq' => 1
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                'min_score' => 10,
                                'from' => $request->skip,
                                'size' => $request->take,
                                '_source' => [ 'url', 'title', 'description', 'created_at' ]
                            ]);
                        break;
                        case 'entry':
                            $documents = Document::list([ 'sozluk', '*' ], $es_type, [
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'more_like_this' => [
                                                    'fields' => [ 'title' ],
                                                    'like' => array_keys($smilar),
                                                    'min_term_freq' => 1,
                                                    'min_doc_freq' => 1
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                'min_score' => 10,
                                'from' => $request->skip,
                                'size' => $request->take,
                                '_source' => [ 'url', 'title', 'entry', 'author', 'created_at' ]
                            ]);
                        break;
                        case 'product':
                            $documents = Document::list([ 'shopping', '*' ], $es_type, [
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'more_like_this' => [
                                                    'fields' => [ 'title' ],
                                                    'like' => array_keys($smilar),
                                                    'min_term_freq' => 1,
                                                    'min_doc_freq' => 1
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                'min_score' => 10,
                                'from' => $request->skip,
                                'size' => $request->take,
                                '_source' => [ 'url', 'title', 'description', 'price', 'breadcrumb', 'created_at' ]
                            ]);
                        break;
                    }
                }
                else
                {
                    return [
                        'status' => 'ok',
                        'hits' => []
                    ];
                }
            }

            return [
                'status' => 'ok',
                'hits' => $documents->data['hits']['hits']
            ];
        }
        else
        {
            return [
                'status' => 'err',
                'data' => [
                    'reason' => 'not found'
                ]
            ];
        }
    }
}
