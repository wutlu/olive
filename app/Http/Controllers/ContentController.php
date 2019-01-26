<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Elasticsearch\Document;

use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\ShoppingCrawler;

use Carbon\Carbon;

use App\Utilities\Term;

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
            $id_segment = explode('-', $es_index);

            switch ($es_type)
            {
                case 'entry':
                    $crawler = SozlukCrawler::where('id', $id_segment[1])->firstOrFail();

                    $title = implode(' ', [ $crawler->name, '/', $document['_source']['title'] ]);
                break;

                case 'product':
                    $crawler = ShoppingCrawler::where('id', $id_segment[1])->firstOrFail();

                    $title = implode(' ', [ $crawler->name, '/', $document['_source']['title'] ]);
                break;

                case 'tweet':
                    $title = implode(' ', [ 'Twitter', 'Tweet', '#'.$es_id ]);
                break;

                case 'video':
                    $title = implode(' ', [ 'YouTube', 'Video', '#'.$es_id ]);
                break;

                case 'comment':
                    $title = implode(' ', [ 'YouTube', 'Comment', '#'.$es_id ]);
                break;

                case 'article':
                    $crawler = MediaCrawler::where('id', $document['_source']['site_id'])->firstOrFail();

                    $data['total'] = Document::count($es_index, 'article', [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [
                                        'match' => [ 'site_id' => $crawler->id ]
                                    ]
                                ]
                            ]
                        ]
                    ]);

                    $data['pos'] = Document::count($es_index, 'article', [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'site_id' => $crawler->id ] ],
                                    [ 'match' => [ 'status' => 'ok' ] ]
                                ],
                                'filter' => [
                                    [ 'range' => [ 'sentiment.pos' => [ 'gte' => .34 ] ] ]
                                ]
                            ]
                        ]
                    ]);

                    $data['neg'] = Document::count($es_index, 'article', [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'site_id' => $crawler->id ] ],
                                    [ 'match' => [ 'status' => 'ok' ] ]
                                ],
                                'filter' => [
                                    [ 'range' => [ 'sentiment.neg' => [ 'gte' => .34 ] ] ]
                                ]
                            ]
                        ]
                    ]);

                    $title = implode(' ', [ $crawler->name, '/', $document['_source']['title'] ]);
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
                $max = 7;
            break;
        }

        $arr = [
                'size' => 0,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'exists' => [ 'field' => 'created_at' ]
                            ]
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
                                'min' => 0,
                                'max' => $max
                            ]
                        ]
                    ]
                ]
        ];

        if ($es_type == 'entry')
        {
            $arr['query']['bool']['must'][] = [
                'match' => [
                    'group_name' => $es_id
                ]
            ];
        }
        else if ($es_type == 'article')
        {
            $arr['query']['bool']['must'][] = [
                'match' => [
                    'site_id' => $es_id
                ]
            ];

            $arr['query']['bool']['must'][] = [
                'exists' => [ 'field' => 'created_at' ]
            ];
        }
        else if ($es_type == 'product')
        {
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
        }

        $document = Document::list($es_index, $es_type, $arr);

        //$document = json_encode(json_decode($document->message), JSON_PRETTY_PRINT); print_r($document); exit();

        return [
            'status' => $document->status,
            'data' => $document->data
        ];
    }
}
