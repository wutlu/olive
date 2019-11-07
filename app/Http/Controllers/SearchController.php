<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis as RedisCache;

use App\Http\Controllers\Controller;

use App\Http\Requests\Search\ArchiveRequest;
use App\Http\Requests\Search\SaveRequest;
use App\Http\Requests\Search\CompareRequest;
use App\Http\Requests\QRequest;
use App\Http\Requests\IdRequest;

use App\Elasticsearch\Document;

use Term;

use App\Models\Currency;
use App\Models\SavedSearch;
use App\Models\Geo\States;
use App\Models\SearchHistory;

use App\Utilities\Crawler;

use Carbon\Carbon;

class SearchController extends Controller
{
    public function __construct()
    {
        ### [ üyelik ve organizasyon zorunlu ] ###
        $this->middleware([ 'auth', 'organisation:have' ]);

        ### [ zorunlu aktif organizasyon ve module_search ] ###
        $this->middleware([
            'can:organisation-status',
            'organisation:have,module_search'
        ])->only([
            'compare',
            'compareProcess',
            'search',
            'aggregation',
            'save'
        ]);

        ### [ zorunlu aktif organizasyon ] ###
        $this->middleware([
            'organisation:have,module_compare'
        ])->only([
            'compare',
            'compareProcess'
        ]);

        ### [ 500 işlemden sonra 5 dakika ile sınırla ] ###
        $this->middleware('throttle:500,5')->only([
            'search',
            'aggregation'
        ]);
    }

    /**
     * Veri Kıyaslama
     *
     * @return view
     */
    public static function compare()
    {
        $organisation = auth()->user()->organisation;

        return view('compare', compact('organisation'));
    }

    /**
     * Veri Kıyaslama İşlem
     *
     * @return array
     */
    public static function compareProcess(CompareRequest $request)
    {
        if (count($request->searches) >= 2)
        {
            $time_model = [];

            $normalize_1 = [];
            $normalize_2 = [];

            if ($request->metric == 'on')
            {
                $begin = new \DateTime($request->start_date.' 00:00:00');
                $end   = new \DateTime($request->start_date.' 23:59:59');

                $start_date = new \DateTime($request->start_date.' 00:00:00');
                $end_date = new \DateTime($request->start_date.' 23:59:59');

                $histogram = [
                    'field' => 'created_at',
                    'interval' => 'hour',
                    'format' => 'HH:mm',
                    'min_doc_count' => 1
                ];

                for ($i = $begin; $i <= $end; $i->modify('+1 hour'))
                {
                    $time_model[$i->format('H:i')] = 0;
                }
            }
            else
            {
                $begin = new \DateTime($request->start_date);
                $end   = new \DateTime($request->end_date);

                $start_date = new \DateTime($request->start_date);
                $end_date = new \DateTime($request->end_date);

                $histogram = [
                    'field' => 'created_at',
                    'interval' => 'day',
                    'format' => 'yyyy-MM-dd',
                    'min_doc_count' => 1
                ];

                for ($i = $begin; $i <= $end; $i->modify('+1 day'))
                {
                    $time_model[$i->format('Y-m-d')] = 0;
                }
            }

            $results = [];

            $organisation = auth()->user()->organisation;

            foreach ($request->searches as $id)
            {
                $search = SavedSearch::where('id', $id)->first();

                if (@$search)
                {
                    $dates = $time_model;
                    $data = [];

                    $clean = Term::cleanSearchQuery($search->string);

                    $q = [
                        'size' => 0,
                        'query' => [
                            'bool' => [
                                'filter' => [
                                    'range' => [
                                        'created_at' => [
                                            'format' => 'YYYY-MM-dd',
                                            'gte' => $start_date->format('Y-m-d'),
                                            'lte' => $end_date->format('Y-m-d')
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
                        ],
                        'aggs' => [
                            'metrics' => [
                                'date_histogram' => $histogram
                            ]
                        ]
                    ];

                    if ($search->category)
                    {
                        $q['query']['bool']['must'][] = [ 'match' => [ 'category' => config('system.analysis.category.types')[$search->category]['title'] ] ];
                    }

                    foreach (
                        [
                            [
                                'consumer' => [
                                    'nws',
                                    'que',
                                    'req',
                                    'cmp'
                                ]
                            ],
                            [
                                'sentiment' => [
                                    'pos',
                                    'neg',
                                    'neu',
                                    'hte'
                                ]
                            ]
                        ] as $key => $bucket
                    )
                    {
                        foreach ($bucket as $key => $b)
                        {
                            foreach ($b as $o)
                            {
                                if ($search->{$key.'_'.$o})
                                {
                                    $q['query']['bool']['must'][] = [
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

                    foreach ($search->modules as $module)
                    {
                        switch ($module)
                        {
                            case 'twitter':
                                if ($organisation->data_twitter)
                                {
                                    $twitter_q = $q;

                                    $data[] = self::tweet($search, $twitter_q)['aggs'];
                                }
                            break;
                            case 'instagram':
                                if ($organisation->data_instagram)
                                {
                                    $instagram_q = $q;

                                    $data[] = self::instagram($search, $instagram_q)['aggs'];
                                }
                            break;
                            case 'sozluk':
                                if ($organisation->data_sozluk)
                                {
                                    $sozluk_q = $q;

                                    $data[] = self::sozluk($search, $sozluk_q)['aggs'];
                                }
                            break;
                            case 'news':
                                if ($organisation->data_news)
                                {
                                    $news_q = $q;

                                    if ($search->state)
                                    {
                                        $news_q['query']['bool']['must'][] = [ 'match' => [ 'state' => $search->state ] ];
                                    }

                                    $data[] = self::news($search, $news_q)['aggs'];
                                }
                            break;
                            case 'blog':
                                if ($organisation->data_blog)
                                {
                                    $blog_q = $q;

                                    $data[] = self::blog($search, $blog_q)['aggs'];
                                }
                            break;
                            case 'youtube_video':
                                if ($organisation->data_youtube_video)
                                {
                                    $youtube_video_q = $q;

                                    $data[] = self::youtube_video($search, $youtube_video_q)['aggs'];
                                }
                            break;
                            case 'youtube_comment':
                                if ($organisation->data_youtube_comment)
                                {
                                    $youtube_comment_q = $q;

                                    $data[] = self::youtube_comment($search, $youtube_comment_q)['aggs'];
                                }
                            break;
                            case 'shopping':
                                if ($organisation->data_shopping)
                                {
                                    $shopping_q = $q;

                                    $data[] = self::shopping($search, $shopping_q)['aggs'];
                                }
                            break;
                    }
                    }

                    foreach ($data as $dt)
                    {
                        if (@$dt['metrics']['buckets'])
                        {
                            foreach ($dt['metrics']['buckets'] as $bucket)
                            {
                                $dates[$bucket['key_as_string']] = $dates[$bucket['key_as_string']] + $bucket['doc_count'];
                            }
                        }
                    }

                    if ($request->normalize_1 == $id && $request->normalize_1)
                    {
                        $normalize_1 = array_values($dates);
                    }

                    if ($request->normalize_2 == $id && $request->normalize_2)
                    {
                        $normalize_2 = array_values($dates);
                    }

                    $results[] = [
                        'name' => $search->name,
                        'data' => array_values($dates)
                    ];
                }
            }

            if (count($normalize_1) && count($normalize_2))
            {
                $metric = [];

                foreach ($normalize_1 as $key => $value)
                {
                    $metric[] = $value - $normalize_2[$key];
                }

                $max = max($metric);
                $min = min($metric);

                $metric = array_map(function($metric) use($max, $min) {
                    try
                    {
                        return round(($metric-$min)/($max-$min), 1);
                    }
                    catch (\Exception $e)
                    {
                        return 0;
                    }
                }, $metric);
            }

            if ($request->currency)
            {
                $currencies = Currency::selectRaw('date_trunc(\''.($request->metric ? 'hour' : 'day').'\', date), max(value)')
                                      ->whereDate('date', '>=', $start_date->format('Y-m-d H:i:s'))
                                      ->whereDate('date', '<=', $end_date->format('Y-m-d H:i:s'))
                                      ->where('key', $request->currency)
                                      ->groupBy(\DB::raw('1'))
                                      ->get();

                if (count($currencies))
                {
                    $cur_arr = $time_model;

                    foreach ($currencies as $key => $cur)
                    {
                        if ($request->metric)
                        {
                            $cur_arr[date('H:i', strtotime($cur->date_trunc))] = round($cur->max, 2);
                        }
                        else
                        {
                            $cur_arr[date('Y-m-d', strtotime($cur->date_trunc))] = round($cur->max, 2);
                        }
                    }
                }

                if (@$cur_arr)
                {
                    $results[] = [
                        'name' => $request->currency,
                        'color' => '#ccc',
                        'data' => array_values($cur_arr),
                        'max' => max($cur_arr),
                        'min' => min($cur_arr)
                    ];
                }
            }

            $return = [
                'status' => 'ok',
                'categories' => array_keys($time_model),
                'datas' => $results,
            ];

            if (@$metric)
            {
                $return['normalized'] = [
                    'name' => 'Normalize',
                    'data' => $metric
                ];
            }

            return $return;
        }
        else
        {
            return [
                'status' => 'failed',
                'reason' => [
                    'title' => 'Eksik Seçim',
                    'text' => 'Lütfen en az 2 kayıtlı arama seçin!'
                ]
            ];
        }
    }

    /**
     * Arama Kaydetme
     *
     * @return array
     */
    public static function save(SaveRequest $request)
    {
        $query = new SavedSearch;
        $query->organisation_id = auth()->user()->organisation_id;
        $query->fill($request->all());
        $query->save();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Arama Silme
     *
     * @return array
     */
    public static function delete(IdRequest $request)
    {
        SavedSearch::where([
            'id' => $request->id,
            'organisation_id' => auth()->user()->organisation_id
        ])->delete();

        return [
            'status' => 'ok',
            'data' => [
                'id' => $request->id
            ]
        ];
    }

    /**
     * Kayıtlı Aramalar
     *
     * @return array
     */
    public static function searches()
    {
        $query = SavedSearch::select([
            'id',
            'name',
            'string',
            'reverse',
            'sharp',

            'sentiment_pos',
            'sentiment_neu',
            'sentiment_neg',
            'sentiment_hte',

            'consumer_que',
            'consumer_req',
            'consumer_cmp',
            'consumer_nws',

            'gender',
            'take',
            'modules',
            'category',
            'state',

            'twitter_sort',
            'twitter_sort_operator'
        ])->where('organisation_id', auth()->user()->organisation_id)->orderBy('id', 'desc')->get();

        return [
            'status' => 'ok',
            'hits' => $query
        ];
    }

    /**
     * Arama Ana Sayfa
     *
     * @return view
     */
    public static function dashboard(QRequest $request)
    {
        $q = $request->q;
        $s = $request->s;
        $e = $request->e;

        $organisation = auth()->user()->organisation;

        $states = States::where('country_id', 223)->orderBy('name', 'ASC')->get();

        $trends = json_decode(RedisCache::get(implode(':', [ config('system.db.alias'), 'trends', 'twitter_hashtag' ])));

        $elements = [
            'start_date',
            'end_date',
            'modules',
            'string',
            'reverse',
            'take',
            'gender',
            'sentiment_pos',
            'sentiment_neu',
            'sentiment_neg',
            'sentiment_hte',
            'consumer_que',
            'consumer_req',
            'consumer_cmp',
            'consumer_nws',
            'sharp',
            'category',
            'state',
            'twitter_sort',
            'twitter_sort_operator'
        ];

        $elements = implode(',', $elements);

        return view('search', compact('q', 's', 'e', 'trends', 'organisation', 'states', 'elements'));
    }

    /**
     * Modül sorgusu
     *
     * @return array
     */
    public static function result_default(array $object)
    {
        $arr = [
            'uuid' => md5($object['_id'].'.'.$object['_index']),
            '_id' => $object['_id'],
            '_type' => $object['_type'],
            '_index' => $object['_index'],

            'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at'])),

            'sentiment' => Crawler::emptySentiment(@$object['_source']['sentiment'])
        ];

        if (@$object['_source']['illegal'])
        {
            $arr['illegal'] = $object['_source']['illegal'];
        }

        if (@$object['_source']['consumer'])
        {
            $arr['consumer'] = $object['_source']['consumer'];
        }

        if (@$object['_source']['deleted_at'])
        {
            $arr['deleted_at'] = date('d.m.Y H:i:s', strtotime($object['_source']['deleted_at']));
        }

        if (@$object['_source']['category'])
        {
            $arr['category'] = $object['_source']['category'];
        }

        return $arr;
    }

    /**
     * Arama Sonuçları
     *
     * @return array
     */
    public static function search(ArchiveRequest $request)
    {
        $user = auth()->user();
        $organisation = $user->organisation;

        $clean = Term::cleanSearchQuery($request->string);

        if ($clean->line && $request->skip == 0)
        {
            $history = SearchHistory::where([ 'query' => $clean->line, 'user_id' => $user->id ])
                                    ->where('created_at', '>=', Carbon::now()->subMinutes(10))
                                    ->exists();

            if (!$history)
            {
                $history = new SearchHistory;
                $history->query = $clean->line;
                $history->user_id = $user->id;
                $history->save();
            }
        }

        $q = [
            'from' => $request->skip,
            'size' => $request->take,
            'sort' => [ 'created_at' => $request->reverse ? 'asc' : 'desc' ],
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

        if ($request->aggs)
        {
            $q['size'] = 0;
            $q['from'] = 0;
        }

        if ($request->category)
        {
            $q['query']['bool']['must'][] = [ 'match' => [ 'category' => config('system.analysis.category.types')[$request->category]['title'] ] ];
        }

        foreach ([ [ 'consumer' => [ 'nws', 'que', 'req', 'cmp' ] ], [ 'sentiment' => [ 'pos', 'neg', 'neu', 'hte' ] ] ] as $key => $bucket)
        {
            foreach ($bucket as $key => $b)
            {
                foreach ($b as $o)
                {
                    if ($request->{$key.'_'.$o})
                    {
                        $q['query']['bool']['must'][] = [
                            'range' => [
                                implode('.', [ $key, $o ]) => [
                                    'gte' => implode('.', [ 0, $request->{$key.'_'.$o} ])
                                ]
                            ]
                        ];
                    }
                }
            }
        }

        $stats = [
            'took' => 0,
            'hits' => 0,
            'counts' => [
                'twitter_tweet' => 0,
                'sozluk_entry' => 0,
                'youtube_video' => 0,
                'youtube_comment' => 0,
                'media_article' => 0,
                'blog_document' => 0,
                'shopping_product' => 0,
                'instagram_media' => 0
            ]
        ];

        $starttime = explode(' ', microtime());
        $starttime = $starttime[1] + $starttime[0];

        $data = [];

        foreach ($request->modules as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
                        $twitter_q = $q;

                        if ($request->twitter_sort)
                        {
                            $twitter_q['sort'] = [ $request->twitter_sort => $request->twitter_sort_operator ];
                        }

                        if ($request->aggs)
                        {
                            $twitter_q['aggs']['mentions'] = [ 'nested' => [ 'path' => 'entities.mentions' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.mentions.mention.id' ] ] ] ];
                            $twitter_q['aggs']['hashtags'] = [ 'nested' => [ 'path' => 'entities.hashtags' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.hashtags.hashtag' ] ] ] ];
                            $twitter_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'user.id' ] ];
                            $twitter_q['aggs']['verified_users'] = [ 'filter' => [ 'exists' => [ 'field' => 'user.verified' ] ] ];
                            $twitter_q['aggs']['followers'] = [ 'avg' => [ 'field' => 'user.counts.followers' ] ];
                            $twitter_q['aggs']['reach'] = [ 'terms' => [ 'field' => 'external.id' ] ];
                        }

                        $tweet_data = self::tweet($request, $twitter_q);

                        if ($request->aggs)
                        {
                            $stats['twitter']['mentions'] = @$tweet_data['aggs']['mentions']['doc_count'];
                            $stats['twitter']['hashtags'] = @$tweet_data['aggs']['hashtags']['doc_count'];
                            $stats['twitter']['unique_users'] = @$tweet_data['aggs']['unique_users']['value'];
                            $stats['twitter']['verified_users'] = @$tweet_data['aggs']['verified_users']['doc_count'];
                            $stats['twitter']['followers'] = @$tweet_data['aggs']['followers']['value'];
                            $stats['twitter']['reach'] = @$tweet_data['aggs']['reach']['sum_other_doc_count'];
                        }

                        $stats['hits'] = $stats['hits'] + $tweet_data['stats']['total'];
                        $stats['counts']['twitter_tweet'] = $tweet_data['stats']['total'];

                        $data = array_merge($data, $tweet_data['data']);
                    }
                break;
                case 'instagram':
                    if ($organisation->data_instagram)
                    {
                        $instagram_q = $q;

                        if ($request->aggs)
                        {
                            $instagram_q['aggs']['mentions'] = [ 'nested' => [ 'path' => 'entities.mentions' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.mentions.mention.id' ] ] ] ];
                            $instagram_q['aggs']['hashtags'] = [ 'nested' => [ 'path' => 'entities.hashtags' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.hashtags.hashtag' ] ] ] ];
                            $instagram_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'user.id' ] ];
                        }

                        $instagram_data = self::instagram($request, $instagram_q);

                        if ($request->aggs)
                        {
                            $stats['instagram']['mentions'] = @$instagram_data['aggs']['mentions']['doc_count'];
                            $stats['instagram']['hashtags'] = @$instagram_data['aggs']['hashtags']['doc_count'];
                            $stats['instagram']['unique_users'] = @$instagram_data['aggs']['unique_users']['value'];
                        }

                        $stats['hits'] = $stats['hits'] + $instagram_data['stats']['total'];
                        $stats['counts']['instagram_media'] = $instagram_data['stats']['total'];

                        $data = array_merge($data, $instagram_data['data']);
                    }
                break;
                case 'sozluk':
                    if ($organisation->data_sozluk)
                    {
                        $sozluk_q = $q;

                        if ($request->aggs)
                        {
                            $sozluk_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'author' ] ];
                            $sozluk_q['aggs']['unique_topics'] = [ 'cardinality' => [ 'field' => 'group_name' ] ];
                        }

                        $sozluk_data = self::sozluk($request, $sozluk_q);

                        if ($request->aggs)
                        {
                            $stats['sozluk']['unique_users'] = @$sozluk_data['aggs']['unique_users']['value'];
                            $stats['sozluk']['unique_topics'] = @$sozluk_data['aggs']['unique_topics']['value'];
                        }

                        $stats['hits'] = $stats['hits'] + $sozluk_data['stats']['total'];
                        $stats['counts']['sozluk_entry'] = $sozluk_data['stats']['total'];

                        $data = array_merge($data, $sozluk_data['data']);
                    }
                break;
                case 'news':
                    if ($organisation->data_news)
                    {
                        $news_q = $q;

                        if ($request->aggs)
                        {
                            $news_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                        }

                        if ($request->state)
                        {
                            $news_q['query']['bool']['must'][] = [ 'match' => [ 'state' => $request->state ] ];
                        }

                        $news_data = self::news($request, $news_q);

                        if ($request->aggs)
                        {
                            $stats['news']['unique_sites'] = @$news_data['aggs']['unique_sites']['value'];
                        }

                        $stats['hits'] = $stats['hits'] + $news_data['stats']['total'];
                        $stats['counts']['media_article'] = $news_data['stats']['total'];

                        $data = array_merge($data, $news_data['data']);
                    }
                break;
                case 'blog':
                    if ($organisation->data_blog)
                    {
                        $blog_q = $q;

                        if ($request->aggs)
                        {
                            $blog_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                        }

                        $blog_data = self::blog($request, $blog_q);

                        if ($request->aggs)
                        {
                            $stats['blog']['unique_sites'] = @$blog_data['aggs']['unique_sites']['value'];
                        }

                        $stats['hits'] = $stats['hits'] + $blog_data['stats']['total'];
                        $stats['counts']['blog_document'] = $blog_data['stats']['total'];

                        $data = array_merge($data, $blog_data['data']);
                    }
                break;
                case 'youtube_video':
                    if ($organisation->data_youtube_video)
                    {
                        $youtube_video_q = $q;

                        if ($request->aggs)
                        {
                            $youtube_video_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'channel.id' ] ];
                            $youtube_video_q['aggs']['hashtags'] = [ 'nested' => [ 'path' => 'tags' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'tags.tag' ] ] ] ];
                        }

                        $youtube_video_data = self::youtube_video($request, $youtube_video_q);

                        if ($request->aggs)
                        {
                            $stats['youtube_video']['unique_users'] = @$youtube_video_data['aggs']['unique_users']['value'];
                            $stats['youtube_video']['hashtags'] = @$youtube_video_data['aggs']['hashtags']['doc_count'];
                        }

                        $stats['hits'] = $stats['hits'] + $youtube_video_data['stats']['total'];
                        $stats['counts']['youtube_video'] = $youtube_video_data['stats']['total'];

                        $data = array_merge($data, $youtube_video_data['data']);
                    }
                break;
                case 'youtube_comment':
                    if ($organisation->data_youtube_comment)
                    {
                        $youtube_comment_q = $q;

                        if ($request->aggs)
                        {
                            $youtube_comment_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'channel.id' ] ];
                            $youtube_comment_q['aggs']['unique_videos'] = [ 'cardinality' => [ 'field' => 'video_id' ] ];
                        }

                        $youtube_comment_data = self::youtube_comment($request, $youtube_comment_q);

                        if ($request->aggs)
                        {
                            $stats['youtube_comment']['unique_users'] = @$youtube_comment_data['aggs']['unique_users']['value'];
                            $stats['youtube_comment']['unique_videos'] = @$youtube_comment_data['aggs']['unique_videos']['value'];
                        }

                        $stats['hits'] = $stats['hits'] + $youtube_comment_data['stats']['total'];
                        $stats['counts']['youtube_comment'] = $youtube_comment_data['stats']['total'];

                        $data = array_merge($data, $youtube_comment_data['data']);
                    }
                break;
                case 'shopping':
                    if ($organisation->data_shopping)
                    {
                        $shopping_q = $q;

                        if ($request->aggs)
                        {
                            $shopping_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                            $shopping_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'seller.name' ] ];
                        }

                        $shopping_data = self::shopping($request, $shopping_q);

                        if ($request->aggs)
                        {
                            $stats['shopping']['unique_sites'] = @$shopping_data['aggs']['unique_sites']['value'];
                            $stats['shopping']['unique_users'] = @$shopping_data['aggs']['unique_users']['value'];
                        }

                        $stats['hits'] = $stats['hits'] + $shopping_data['stats']['total'];
                        $stats['counts']['shopping_product'] = $shopping_data['stats']['total'];

                        $data = array_merge($data, $shopping_data['data']);
                    }
                break;
            }
        }

        $mtime = explode(' ', microtime());
        $totaltime = $mtime[0] + $mtime[1] - $starttime;

        if (count($data))
        {
            $stats['took'] = sprintf('%0.2f', $totaltime);
        }

        if ($request->twitter_sort)
        {
            $data = array_reverse($data);
        }
        else
        {
            usort($data, '\App\Utilities\DateUtility::dateSort');
        }

        if (!$request->reverse)
        {
            $data = array_reverse($data);
        }

        return [
            'status' => 'ok',
            'hits' => $data,
            'words' => $clean->words,
            'stats' => $stats
        ];
    }

    public static function tweet($search, array $q)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        if ($search->gender != 'all')
        {
            $q['query']['bool']['should'][] = [ 'match' => [ 'user.gender' => $search->gender ] ];
            $q['query']['bool']['minimum_should_match'] = 1;
        }

        if ($search->sharp)
        {
            $q['query']['bool']['must_not'][] = [ 'match' => [ 'external.type' => 'retweet' ] ];
            $q['query']['bool']['must'][] = [ 'range' => [ 'counts.hashtag' => [ 'lte' => 2 ] ] ];
            $q['query']['bool']['must'][] = [ 'range' => [ 'illegal.nud' => [ 'lte' => 0.4 ] ] ];
            $q['query']['bool']['must'][] = [ 'range' => [ 'illegal.bet' => [ 'lte' => 0.4 ] ] ];
        }

        $query = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', $q);

        if (@$query->data['aggregations'])
        {
            $aggs = $query->data['aggregations'];
        }

        if (@$query->data['hits']['hits'])
        {
            $stats['total'] = $query->data['hits']['total'];

            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = self::result_default($object);

                $user = [
                    'name' => $object['_source']['user']['name'],
                    'screen_name' => $object['_source']['user']['screen_name'],
                    'image' => $object['_source']['user']['image'],
                    'counts' => $object['_source']['user']['counts']
                ];

                if (@$object['_source']['user']['description'])
                {
                    $user['description'] = $object['_source']['user']['description'];
                }

                if (@$object['_source']['user']['verified'])
                {
                    $user['verified'] = true;
                }

                if (@$object['_source']['entities']['medias'])
                {
                    $arr['medias'] = $object['_source']['entities']['medias'];
                }

                if (@$object['_source']['place'])
                {
                    $arr['place'] = $object['_source']['place'];
                }

                $data[] = array_merge($arr, [
                    'user' => $user,
                    'text' => Term::tweet($object['_source']['text']),
                    'counts' => $object['_source']['counts']
                ]);
            }
        }

        return [
            'data' => $data,
            'stats' => $stats,
            'aggs' => $aggs
        ];
    }

    public static function instagram($search, array $q)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        $query = Document::search([ 'instagram', 'medias', '*' ], 'media', $q);

        if (@$query->data['aggregations'])
        {
            $aggs = $query->data['aggregations'];
        }

        if (@$query->data['hits']['hits'])
        {
            $stats['total'] = $query->data['hits']['total'];

            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = self::result_default($object);

                $arr['display_url'] = $object['_source']['display_url'];
                $arr['url'] = 'https://www.instagram.com/p/'.$object['_source']['shortcode'].'/';

                if (@$object['_source']['text'])
                {
                    $arr['text'] = Term::instagramMedia($object['_source']['text']);
                }

                if (@$object['_source']['place'])
                {
                    $arr['place'] = $object['_source']['place'];
                }

                $data[] = $arr;
            }
        }

        return [
            'data' => $data,
            'stats' => $stats,
            'aggs' => $aggs
        ];
    }

    public static function sozluk($search, array $q)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        if ($search->gender != 'all')
        {
            $q['query']['bool']['should'][] = [ 'match' => [ 'gender' => $search->gender ] ];
            $q['query']['bool']['minimum_should_match'] = 1;
        }

        $query = Document::search([ 'sozluk', '*' ], 'entry', $q);

        if (@$query->data['aggregations'])
        {
            $aggs = $query->data['aggregations'];
        }

        if (@$query->data['hits']['hits'])
        {
            $stats['total'] = $query->data['hits']['total'];

            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = self::result_default($object);

                $data[] = array_merge($arr, [
                    'url' => $object['_source']['url'],
                    'title' => $object['_source']['title'],
                    'text' => $object['_source']['entry'],
                    'author' => $object['_source']['author'],
                ]);
            }
        }

        return [
            'data' => $data,
            'stats' => $stats,
            'aggs' => $aggs
        ];
    }

    public static function news($search, array $q)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

        $query = Document::search([ 'media', 's*' ], 'article', $q);

        if (@$query->data['aggregations'])
        {
            $aggs = $query->data['aggregations'];
        }

        if (@$query->data['hits']['hits'])
        {
            $stats['total'] = $query->data['hits']['total'];

            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = self::result_default($object);

                $arr['url'] = $object['_source']['url'];
                $arr['title'] = $object['_source']['title'];
                $arr['text'] = $object['_source']['description'];

                if (@$object['_source']['image_url'])
                {
                    $arr['image'] = $object['_source']['image_url'];
                }

                $data[] = $arr;
            }
        }

        return [
            'data' => $data,
            'stats' => $stats,
            'aggs' => $aggs
        ];
    }

    public static function blog($search, array $q)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

        $query = Document::search([ 'blog', 's*' ], 'document', $q);

        if (@$query->data['aggregations'])
        {
            $aggs = $query->data['aggregations'];
        }

        if (@$query->data['hits']['hits'])
        {
            $stats['total'] = $query->data['hits']['total'];

            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = self::result_default($object);

                $arr['url'] = $object['_source']['url'];
                $arr['title'] = $object['_source']['title'];
                $arr['text'] = $object['_source']['description'];

                if (@$object['_source']['image_url'])
                {
                    $arr['image'] = $object['_source']['image_url'];
                }

                $data[] = $arr;
            }
        }

        return [
            'data' => $data,
            'stats' => $stats,
            'aggs' => $aggs
        ];
    }

    public static function youtube_video($search, array $q)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        $query = Document::search([ 'youtube', 'videos' ], 'video', $q);

        if (@$query->data['aggregations'])
        {
            $aggs = $query->data['aggregations'];
        }

        if (@$query->data['hits']['hits'])
        {
            $stats['total'] = $query->data['hits']['total'];

            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = self::result_default($object);

                $data[] = array_merge($arr, [
                    'title' => $object['_source']['title'],
                    'text' => @$object['_source']['description'],
                    'channel' => [
                        'id' => $object['_source']['channel']['id'],
                        'title' => $object['_source']['channel']['title']
                    ],
                ]);
            }
        }

        return [
            'data' => $data,
            'stats' => $stats,
            'aggs' => $aggs
        ];
    }

    public static function youtube_comment($search, array $q)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        $query = Document::search([ 'youtube', 'comments', '*' ], 'comment', $q);

        if (@$query->data['aggregations'])
        {
            $aggs = $query->data['aggregations'];
        }

        if (@$query->data['hits']['hits'])
        {
            $stats['total'] = $query->data['hits']['total'];

            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = self::result_default($object);

                $data[] = array_merge($arr, [
                    'video_id' => $object['_source']['video_id'],
                    'channel' => [
                        'id' => $object['_source']['channel']['id'],
                        'title' => $object['_source']['channel']['title']
                    ],
                    'text' => $object['_source']['text'],
                ]);
            }
        }

        return [
            'data' => $data,
            'stats' => $stats,
            'aggs' => $aggs
        ];
    }

    public static function shopping($search, array $q)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

        $query = Document::search([ 'shopping', '*' ], 'product', $q);

        if (@$query->data['aggregations'])
        {
            $aggs = $query->data['aggregations'];
        }

        if (@$query->data['hits']['hits'])
        {
            $stats['total'] = $query->data['hits']['total'];

            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = self::result_default($object);

                if (@$object['_source']['description'])
                {
                    $arr['text'] = $object['_source']['description'];
                }

                $data[] = array_merge($arr, [
                    'url' => $object['_source']['url'],
                    'title' => $object['_source']['title'],
                ]);
            }
        }

        return [
            'data' => $data,
            'stats' => $stats,
            'aggs' => $aggs
        ];
    }
}
