<?php

namespace App\Http\Controllers\RealTime;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Http\Requests\RealTime\RealTimeRequest;

use App\Elasticsearch\Document;

use Carbon\Carbon;

use App\Models\SavedSearch;

use Term;

use App\Utilities\Crawler;

use App\Http\Controllers\SearchController;

class RealTimeController extends Controller
{
    /**
     * Temel sorgu.
     *
     * @var array
     */
    private $query;

    public function __construct()
    {
        ### [ üyelik ve organizasyon zorunlu ve organizasyonun zorunlu olarak real_time özelliği desteklemesi ] ###
        $this->middleware([ 'auth', 'organisation:have' ])->except('querySample');

        ### [ zorunlu aktif organizasyon ] ###
        $this->middleware([
            'can:organisation-status',
            'organisation:have,module_real_time'
        ])->only([
            'query'
        ]);
    }

    /**
     * Gerçek Zamanlı, akış ekranı.
     *
     * @return view
     */
    public function stream()
    {
        $organisation = auth()->user()->organisation;

        return view('stream', compact('organisation'));
    }

    /**
     * Gerçek Zamanlı, akış sorgusu.
     *
     * @return array
     */
    public function query(Request $request)
    {
        $request->validate([
            'keyword_group' => 'required|integer'
        ]);

        $organisation = auth()->user()->organisation;

        $search = SavedSearch::where([ 'id' => $request->keyword_group, 'organisation_id' => $organisation->id ])->first();

        if (@$search)
        {
            $clean = Term::cleanSearchQuery($search->string);
            $searchController = new SearchController;

            $q = [
                'size' => 1000,
                'sort' => [ 'created_at' => 'desc' ],
                'query' => [
                    'bool' => [
                        'filter' => [
                            'range' => [
                                'called_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => Carbon::now()->subMinutes(5)->format('Y-m-d H:i')
                                ]
                            ]
                        ],
                        'must' => [
                            [ 'exists' => [ 'field' => 'created_at' ] ],
                            [
                                'query_string' => [
                                    'fields' => [
                                        'title',
                                        'description',
                                        'entry',
                                        'text'
                                    ],
                                    'query' => $clean->line,
                                    'default_operator' => 'AND'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            if ($search->category)
            {
                $q['query']['bool']['must'][] = [ 'match' => [ 'category' => config('system.analysis.category.types')[$search->category]['title'] ] ];
            }

            foreach ([ [ 'consumer' => [ 'nws', 'que', 'req', 'cmp' ] ], [ 'sentiment' => [ 'pos', 'neg', 'neu', 'hte' ] ] ] as $key => $bucket)
            {
                foreach ($bucket as $key => $b)
                {
                    foreach ($b as $o)
                    {
                        if ($search->{$key.'_'.$o})
                        {
                            $q['query']['bool']['filter'][] = [
                                'range' => [
                                    implode('.', [ $key, $o ]) => [
                                        'gte' => implode('.', [ 0, $search->{$key.'_'.$o} ])
                                    ]
                                ]
                            ];
                        }
                    }
                }
            }

            $data = [];

            foreach ($search->modules as $module)
            {
                switch ($module)
                {
                    case 'twitter':
                        if ($organisation->data_twitter)
                        {
                            $tweet_q = $q;

                            unset($tweet_q['query']['bool']['filter']['range']['called_at']);

                            $tweet_q['query']['bool']['filter']['range']['created_at'] = [
                                'format' => 'YYYY-MM-dd HH:mm',
                                'gte' => Carbon::now()->subMinutes(2)->format('Y-m-d H:i')
                            ];

                            $data = array_merge($data, $searchController->tweet($search, $tweet_q)['data']);
                        }
                    break;
                    case 'instagram'       : if ($organisation->data_instagram)       $data = array_merge($data, $searchController->instagram      ($search, $q)['data']); break;
                    case 'sozluk'          : if ($organisation->data_sozluk)          $data = array_merge($data, $searchController->sozluk         ($search, $q)['data']); break;
                    case 'news':
                        if ($organisation->data_news)
                        {
                            $news_q = $q;

                            if ($search->state)
                            {
                                $news_q['query']['bool']['must'][] = [ 'match' => [ 'state' => $search->state ] ];
                            }

                            $data = array_merge($data, $searchController->news($search, $news_q)['data']);
                        }
                    break;
                    case 'blog'            : if ($organisation->data_blog)            $data = array_merge($data, $searchController->blog           ($search, $q)['data']); break;
                    case 'youtube_video'   : if ($organisation->data_youtube_video)   $data = array_merge($data, $searchController->youtube_video  ($search, $q)['data']); break;
                    case 'youtube_comment' : if ($organisation->data_youtube_comment) $data = array_merge($data, $searchController->youtube_comment($search, $q)['data']); break;
                    case 'shopping'        : if ($organisation->data_shopping)        $data = array_merge($data, $searchController->shopping       ($search, $q)['data']); break;
                }
            }

            usort($data, '\App\Utilities\DateUtility::dateSort');

            return [
                'status' => 'ok',
                'data' => $data,
                'words' => $clean->words
            ];
        }
        else
        {
            return [
                'status' => 'err',
                'message' => 'Kayıtlı Aramaya ulaşılamıyor. Lütfen sayfayı yenileyin ve tekrar deneyin.'
            ];
        }
    }

    /**
     * Gerçek Zamanlı, akış sorgusu. (Örnek)
     *
     * @return array
     */
    public function querySample()
    {
        $data = [];
        $words = [
            'bilgi',
            'teknoloji',
            'türkiye',
            'internet',
            'spor',
            'futbol',
            'basketbol'
        ];

        $query = Document::search([ 'media', '*' ], 'article', [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'called_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => Carbon::now()->subMinutes(2)->format('Y-m-d H:i')
                                ]
                            ]
                        ]
                    ],
                    'should' => [
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ],
                    'must' => [
                        [ 'exists' => [ 'field' => 'created_at' ] ],
                        [
                            'query_string' => [
                                'fields' => [
                                    'title',
                                    'description'
                                ],
                                'query' => implode(' ', $words),
                                'default_operator' => 'OR'
                            ]
                        ]
                    ]
                ]
            ],
            'sort' => [ 'created_at' => 'DESC' ],
            'size' => 100,
            '_source' => [
                'url',
                'title',
                'created_at',
                'called_at'
            ]
        ]);

        if (@$query->data['hits']['hits'])
        {
            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = [
                    'uuid' => md5($object['_id'].'.'.$object['_index']),

                    '_id' => $object['_id'],
                    '_type' => $object['_type'],
                    '_index' => $object['_index'],

                    'called_at' => date('H:i', strtotime($object['_source']['called_at']))
                ];

                $data[] = array_merge($arr, [
                    'title' => $object['_source']['title']
                ]);
            }
        }

        return [
            'status' => 'ok',
            'data' => $data,
            'words' => $words
        ];
    }
}
