<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Search\ArchiveRequest;
use App\Http\Requests\Search\ArchiveAggregationRequest;
use App\Http\Requests\QRequest;

use App\Elasticsearch\Document;

use Term;

use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\ShoppingCrawler;

use Illuminate\Support\Facades\Redis as RedisCache;

class SearchController extends Controller
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
        $this->middleware([ 'auth', 'organisation:have' ]);

        ### [ zorunlu aktif organizasyon ] ###
        $this->middleware([
            'can:organisation-status',
            'organisation:have,module_search'
        ])->only([
            'search',
            'aggregation'
        ]);

        ### [ 500 işlemden sonra 5 dakika ile sınırla ] ###
        $this->middleware('throttle:500,5')->only([
            'search',
            'aggregation'
        ]);
    }

    /**
     * Arama Sonuçları için Aggregation
     *
     * @return view
     */
    public static function aggregation(ArchiveAggregationRequest $request)
    {
        $clean = Term::cleanSearchQuery($request->string);

        $mquery = [
            'size' => 0,
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

        if ($request->media)
        {
            $mquery['query']['bool']['must'][] = [
                'nested' => [
                    'path' => 'entities.medias',
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'exists' => [
                                        'field' => 'entities.medias.media.type'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        switch ($request->retweet)
        {
            case 'tweet':
                $mquery['query']['bool']['must_not'][] = [ 'exists' => [ 'field' => 'external.type' ] ];
            break;
            case 'quote':
                $mquery['query']['bool']['must'][] = [ 'match' => [ 'external.type' => 'quote' ] ];
            break;
            case 'reply':
                $mquery['query']['bool']['must'][] = [ 'match' => [ 'external.type' => 'reply' ] ];
            break;
        }

        if ($request->verified)
        {
            $mquery['query']['bool']['must'][] = [ 'exists' => [ 'field' => 'user.verified' ] ];
        }

        if ($request->sentiment != 'all')
        {
            $mquery['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.34 ] ] ];
        }

        $modules = [];

        $organisation = auth()->user()->organisation;

        foreach ($request->modules as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
                        $modules[] = 'tweet';
                    }
                break;
                case 'sozluk':
                    if ($organisation->data_sozluk)
                    {
                        $modules[] = 'entry';
                    }
                break;
                case 'news':
                    if ($organisation->data_news)
                    {
                        $modules[] = 'article';
                    }
                break;
                case 'youtube_video':
                    if ($organisation->data_youtube_video)
                    {
                        $modules[] = 'video';
                    }
                break;
                case 'youtube_comment':
                    if ($organisation->data_youtube_comment)
                    {
                        $modules[] = 'comment';
                    }
                break;
                case 'shopping':
                    if ($organisation->data_shopping)
                    {
                        $modules[] = 'product';
                    }
                break;
            }
        }

        switch ($request->type)
        {
            case 'hourly':
                $arr = array_merge($mquery, [
                    'aggs' => [
                        'results' => [
                            'histogram' => [
                                'script' => 'doc.created_at.value.getHourOfDay()',
                                'interval' => 1
                            ]
                        ]
                    ]
                ]);

                $query = Document::search([ '*' ], implode(',', $modules), $arr);

                $data = [
                    'results' => @$query->data['aggregations']['results']['buckets'],
                    'hits' => @$query->data['hits']['total']
                ];
            break;

            case 'daily':
                $arr = array_merge($mquery, [
                    'aggs' => [
                        'results' => [
                            'histogram' => [
                                'script' => 'doc.created_at.value.getDayOfWeek()',
                                'interval' => 1
                            ]
                        ]
                    ]
                ]);

                $query = Document::search([ '*' ], implode(',', $modules), $arr);

                $data = [
                    'results' => @$query->data['aggregations']['results']['buckets'],
                    'hits' => @$query->data['hits']['total']
                ];
            break;

            case 'location':
                $arr = array_merge($mquery, [
                    'aggs' => [
                        'places' => [
                            'terms' => [
                                'field' => 'place.full_name',
                                'size' => 7
                            ]
                        ]
                    ]
                ]);

                $query = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', $arr);

                $data = [
                    'results' => @$query->data['aggregations']['places']['buckets'],
                    'hits' => @$query->data['hits']['total']
                ];
            break;

            case 'platform':
                $arr = array_merge($mquery, [
                    'aggs' => [
                        'platforms' => [
                            'terms' => [
                                'field' => 'platform',
                                'size' => 7
                            ]
                        ]
                    ]
                ]);

                $query = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', $arr);

                $data = [
                    'results' => @$query->data['aggregations']['platforms']['buckets'],
                    'hits' => @$query->data['hits']['total']
                ];
            break;

            case 'mention':
                $arr = [];

                foreach ($request->modules as $module)
                {
                    switch ($module)
                    {
                        case 'twitter':
                            $document = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', array_merge($mquery, [
                                'aggs' => [
                                    'results' => [
                                        'terms' => [
                                            'field' => 'user.id',
                                            'size' => 10
                                        ],
                                        'aggs' => [
                                            'properties' => [
                                                'top_hits' => [
                                                    'size' => 1,
                                                    '_source' => [
                                                        'include' => [
                                                            'user.name',
                                                            'user.screen_name'
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]));

                            if (@$document->data['aggregations']['results']['buckets'])
                            {
                                $arr['twitter'] = array_map(function($arr) {
                                    return [
                                        'key' => $arr['key'],
                                        'name' => $arr['properties']['hits']['hits'][0]['_source']['user']['name'],
                                        'screen_name' => $arr['properties']['hits']['hits'][0]['_source']['user']['screen_name'],
                                        'doc_count' => $arr['doc_count']
                                    ];
                                }, $document->data['aggregations']['results']['buckets']);
                            }

                            if (@$document->data['aggregations']['results']['hit_items']['buckets'])
                            {
                                $arr['twitter_out'] = array_map(function($arr) {
                                    return [
                                        'key' => $arr['key'],
                                        'name' => $arr['properties']['hits']['hits'][0]['_source']['mention']['name'],
                                        'screen_name' => $arr['properties']['hits']['hits'][0]['_source']['mention']['screen_name'],
                                        'doc_count' => $arr['doc_count']
                                    ];
                                }, $document->data['aggregations']['results']['hit_items']['buckets']);
                            }

                            ############################

                            $document = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', array_merge($mquery, [
                                'aggs' => [
                                    'results' => [
                                        'nested' => [ 'path' => 'entities.mentions' ],
                                        'aggs' => [
                                            'hit_items' => [
                                                'terms' => [
                                                    'field' => 'entities.mentions.mention.id',
                                                    'size' => 15
                                                ],
                                                'aggs' => [
                                                    'properties' => [
                                                        'top_hits' => [
                                                            'size' => 1,
                                                            '_source' => [
                                                                'include' => [
                                                                    'entities.mentions.mention.name',
                                                                    'entities.mentions.mention.screen_name'
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]));

                            if (@$document->data['aggregations']['results']['hit_items']['buckets'])
                            {
                                $arr['twitter_out'] = array_map(function($arr) {
                                    return [
                                        'key' => $arr['key'],
                                        'name' => $arr['properties']['hits']['hits'][0]['_source']['mention']['name'],
                                        'screen_name' => $arr['properties']['hits']['hits'][0]['_source']['mention']['screen_name'],
                                        'doc_count' => $arr['doc_count']
                                    ];
                                }, $document->data['aggregations']['results']['hit_items']['buckets']);
                            }
                        break;
                        case 'news':
                        case 'shopping':
                            switch ($module)
                            {
                                case 'shopping':
                                    $index = [ 'shopping', '*' ];
                                    $type = 'product';
                                    $site = new ShoppingCrawler;
                                break;
                                case 'news':
                                    $index = [ 'media', 's*' ];
                                    $type = 'article';
                                    $site = new MediaCrawler;
                                break;
                            }

                            $document = Document::search($index, $type, array_merge($mquery, [
                                'aggs' => [
                                    'results' => [
                                        'terms' => [
                                            'field' => 'site_id',
                                            'size' => 10
                                        ]
                                    ]
                                ]
                            ]));

                            if (@$document->data['aggregations']['results']['buckets'])
                            {
                                $arr[$module] = array_map(function($item) use ($site) {
                                    $site = $site->where('id', $item['key'])->first();

                                    return @$site ? [
                                        'key' => $item['key'],
                                        'name' => $site['name'],
                                        'site' => $site['site'],
                                        'doc_count' => $item['doc_count']
                                    ] : [
                                        'key' => $item['key'],
                                        'name' => 'N/A ('.$item['key'].')',
                                        'site' => null,
                                        'doc_count' => $item['doc_count']
                                    ];
                                }, $document->data['aggregations']['results']['buckets']);
                            }
                        break;
                        case 'youtube_video':
                        case 'youtube_comment':
                            switch ($module)
                            {
                                case 'youtube_video':
                                    $index = [ 'youtube', 'videos' ];
                                    $type = 'video';
                                break;
                                case 'youtube_comment':
                                    $index = [ 'youtube', 'comments', '*' ];
                                    $type = 'comment';
                                break;
                            }

                            $document = Document::search($index, $type, array_merge($mquery, [
                                'aggs' => [
                                    'results' => [
                                        'terms' => [
                                            'field' => 'channel.id',
                                            'size' => 10
                                        ],
                                        'aggs' => [
                                            'properties' => [
                                                'top_hits' => [
                                                    'size' => 1,
                                                    '_source' => [
                                                        'include' => [
                                                            'channel.title'
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]));

                            if (@$document->data['aggregations']['results']['buckets'])
                            {
                                $arr[$module] = array_map(function($arr) {
                                    return [
                                        'key' => $arr['key'],
                                        'title' => $arr['properties']['hits']['hits'][0]['_source']['channel']['title'],
                                        'doc_count' => $arr['doc_count']
                                    ];
                                }, $document->data['aggregations']['results']['buckets']);
                            }
                        break;
                        case 'sozluk':
                            $document = Document::search([ 'sozluk', '*' ], 'entry', array_merge($mquery, [
                                'aggs' => [
                                    'results' => [
                                        'terms' => [
                                            'field' => 'author',
                                            'size' => 10
                                        ]
                                    ]
                                ]
                            ]));

                            if (@$document->data['aggregations']['results']['buckets'])
                            {
                                $arr['sozluk'] = array_map(function($arr) {
                                    return [
                                        'key' => $arr['key'],
                                        'slug' => str_slug($arr['key']),
                                        'doc_count' => $arr['doc_count']
                                    ];
                                }, $document->data['aggregations']['results']['buckets']);
                            }
                        break;
                    }
                }

                $data = $arr;
            break;

            case 'sentiment':
                $query = Document::search([ '*' ], implode(',', $modules), array_merge($mquery, [
                    'aggs' => [
                        'positive' => [ 'avg' => [ 'field' => 'sentiment.pos' ] ],
                        'neutral' => [ 'avg' => [ 'field' => 'sentiment.neu' ] ],
                        'negative' => [ 'avg' => [ 'field' => 'sentiment.neg' ] ]
                    ]
                ]));

                $data = [
                    'results' => @$query->data['aggregations'],
                    'hits' => @$query->data['hits']['total']
                ];
            break;

            case 'hashtag':
                $arr = array_merge($mquery, [
                    'aggs' => [
                        'hashtag' => [
                            'nested' => [ 'path' => 'entities.hashtags' ],
                            'aggs' => [
                                'hit_items' => [
                                    'terms' => [
                                        'field' => 'entities.hashtags.hashtag',
                                        'size' => 7
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);

                $query = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', $arr);

                $data = [
                    'results' => @$query->data['aggregations']['hashtag']['hit_items']['buckets'],
                    'hits' => @$query->data['hits']['total']
                ];
            break;

            case 'source':
                $arr = $mquery;

                unset($arr['size']);

                foreach ($modules as $module)
                {
                    switch ($module)
                    {
                        case 'tweet':
                            $index = [ 'twitter', 'tweets', '*' ];
                            $title = 'Twitter (tweet)';
                        break;
                        case 'article':
                            $index = [ 'media', 's*' ];
                            $title = 'Medya (haber)';
                        break;
                        case 'comment':
                            $index = [ 'youtube', 'comments', '*' ];
                            $title = 'YouTube (yorum)';
                        break;
                        case 'video':
                            $index = [ 'youtube', 'videos' ];
                            $title = 'YouTube (video)';
                        break;
                        case 'entry':
                            $index = [ 'sozluk', '*' ];
                            $title = 'Sözlük (girdi)';
                        break;
                        case 'product':
                            $index = [ 'shopping', '*' ];
                            $title = 'E-ticaret (ürün)';
                        break;
                    }

                    $data[$title] = @Document::count($index, $module, $arr)->data['count'];
                }

            break;
        }

        return [
            'status' => 'ok',
            'data' => $data
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

        $trends = json_decode(RedisCache::get(implode(':', [ config('system.db.alias'), 'trends', 'twitter' ])));

        return view('search', compact('q', 's', 'e', 'trends', 'organisation'));
    }

    /**
     * Arama Sonuçları
     *
     * @return array
     */
    public static function search(ArchiveRequest $request)
    {
        $data = [];

        $clean = Term::cleanSearchQuery($request->string);

        $q = [
            'from' => $request->skip,
            'size' => $request->take,
            'sort' => [ 'created_at' => $request->sort ? $request->sort : 'desc' ],
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
                    ],
                    'should' => [
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ]
                ]
            ],
            '_source' => [
                'user.name',
                'user.screen_name',
                'user.image',
                'user.verified',
                'text',

                'created_at',
                'deleted_at',

                'url',
                'title',
                'description',
                'image_url',

                'entry',
                'author',

                'channel.title',
                'channel.id',

                'video_id',
                'sentiment'
            ]
        ];

        if ($request->media)
        {
            $q['query']['bool']['must'][] = [
                'nested' => [
                    'path' => 'entities.medias',
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'exists' => [
                                        'field' => 'entities.medias.media.type'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        switch ($request->retweet)
        {
            case 'tweet':
                $q['query']['bool']['must_not'][] = [ 'exists' => [ 'field' => 'external.type' ] ];
            break;
            case 'quote':
                $q['query']['bool']['must'][] = [ 'match' => [ 'external.type' => 'quote' ] ];
            break;
            case 'reply':
                $q['query']['bool']['must'][] = [ 'match' => [ 'external.type' => 'reply' ] ];
            break;
        }

        if ($request->verified)
        {
            $q['query']['bool']['must'][] = [ 'exists' => [ 'field' => 'user.verified' ] ];
        }

        if ($request->sentiment != 'all')
        {
            $q['query']['bool']['filter'][] = [
                'range' => [ implode('.', [ 'sentiment', $request->sentiment ]) => [ 'gte' => 0.34 ] ]
            ];
        }

        $modules = [];

        $organisation = auth()->user()->organisation;

        foreach ($request->modules as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
                        $modules[] = 'tweet';
                    }
                break;
                case 'sozluk':
                    if ($organisation->data_sozluk)
                    {
                        $modules[] = 'entry';
                    }
                break;
                case 'news':
                    if ($organisation->data_news)
                    {
                        $modules[] = 'article';
                    }
                break;
                case 'youtube_video':
                    if ($organisation->data_youtube_video)
                    {
                        $modules[] = 'video';
                    }
                break;
                case 'youtube_comment':
                    if ($organisation->data_youtube_comment)
                    {
                        $modules[] = 'comment';
                    }
                break;
                case 'shopping':
                    if ($organisation->data_shopping)
                    {
                        $modules[] = 'product';
                    }
                break;
            }
        }

        $query = Document::search([ '*' ], implode(',', $modules), $q);

        $stats = [
            'took' => 0,
            'hits' => 0,
        ];

        if (@$query->data['hits']['hits'])
        {
            $stats['took'] = $query->data['took']/1000;
            $stats['hits'] = number_format($query->data['hits']['total']);

            foreach ($query->data['hits']['hits'] as $object)
            {
                $arr = [
                    'uuid' => md5($object['_id'].'.'.$object['_index']),
                    '_id' => $object['_id'],
                    '_type' => $object['_type'],
                    '_index' => $object['_index'],

                    'created_at' => date('d.m.Y H:i:s', strtotime($object['_source']['created_at'])),

                    'sentiment' => $object['_source']['sentiment']
                ];

                if (@$object['_source']['deleted_at'])
                {
                    $arr['deleted_at'] = date('d.m.Y H:i:s', strtotime($object['_source']['deleted_at']));
                }

                switch ($object['_type'])
                {
                    case 'tweet':
                        $user = [
                            'name' => $object['_source']['user']['name'],
                            'screen_name' => $object['_source']['user']['screen_name'],
                            'image' => $object['_source']['user']['image']
                        ];

                        if (@$object['_source']['user']['verified'])
                        {
                            $user['verified'] = true;
                        }

                        $data[] = array_merge($arr, [
                            'user' => $user,
                            'text' => Term::tweet($object['_source']['text']),
                        ]);
                    break;
                    case 'article':
                        $article = [
                            'url' => $object['_source']['url'],
                            'title' => $object['_source']['title'],
                            'text' => $object['_source']['description'],
                        ];

                        if (@$object['_source']['image_url'])
                        {
                            $article['image'] = $object['_source']['image_url'];
                        }

                        $data[] = array_merge($arr, $article);
                    break;
                    case 'entry':
                        $data[] = array_merge($arr, [
                            'url' => $object['_source']['url'],
                            'title' => $object['_source']['title'],
                            'text' => $object['_source']['entry'],
                            'author' => $object['_source']['author'],
                        ]);
                    break;
                    case 'product':
                        if (@$object['_source']['description'])
                        {
                            $arr['text'] = $object['_source']['description'];
                        }

                        $data[] = array_merge($arr, [
                            'url' => $object['_source']['url'],
                            'title' => $object['_source']['title'],
                        ]);
                    break;
                    case 'video':
                        $data[] = array_merge($arr, [
                            'title' => $object['_source']['title'],
                            'text' => @$object['_source']['description'],
                            'channel' => [
                                'id' => $object['_source']['channel']['id'],
                                'title' => $object['_source']['channel']['title']
                            ],
                        ]);
                    break;
                    case 'comment':
                        $data[] = array_merge($arr, [
                            'video_id' => $object['_source']['video_id'],
                            'channel' => [
                                'id' => $object['_source']['channel']['id'],
                                'title' => $object['_source']['channel']['title']
                            ],
                            'text' => $object['_source']['text'],
                        ]);
                    break;
                }
            }
        }

        return [
            'status' => 'ok',
            'hits' => $data,
            'words' => $clean->words,
            'stats' => $stats
        ];
    }
}
