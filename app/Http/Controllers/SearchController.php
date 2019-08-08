<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis as RedisCache;

use App\Http\Controllers\Controller;
use App\Http\Requests\Search\ArchiveRequest;
use App\Http\Requests\Search\ArchiveAggregationRequest;
use App\Http\Requests\Search\SaveRequest;
use App\Http\Requests\QRequest;
use App\Http\Requests\IdRequest;

use App\Elasticsearch\Document;

use Term;

use App\Models\Source;
use App\Models\SavedSearch;

use App\Utilities\Crawler;

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
        ### [ üyelik ve organizasyon zorunlu ve organizasyonun zorunlu ] ###
        $this->middleware([ 'auth', 'organisation:have' ]);

        ### [ zorunlu aktif organizasyon ] ###
        $this->middleware([
            'can:organisation-status',
            'organisation:have,module_search'
        ])->only([
            'search',
            'aggregation',
            'save'
        ]);

        ### [ 500 işlemden sonra 5 dakika ile sınırla ] ###
        $this->middleware('throttle:500,5')->only([
            'search',
            'aggregation'
        ]);
    }

    /**
     * Arama Kaydetme
     *
     * @return array
     */
    public static function save(SaveRequest $request)
    {
        $request['modules'] = json_encode($request->modules);

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
            'modules'
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

        $trends = json_decode(RedisCache::get(implode(':', [ config('system.db.alias'), 'trends', 'twitter_hashtag' ])));

        return view('search', compact('q', 's', 'e', 'trends', 'organisation'));
    }

    /**
     * Modül sorgusu
     *
     * @return array
     */
    private static function query_build(array $object)
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

        return $arr;
    }

    /**
     * Arama Sonuçları
     *
     * @return array
     */
    public static function search(ArchiveRequest $request)
    {
        $data = [];
        $modules = [];

        $organisation = auth()->user()->organisation;

        preg_match_all('/(?<=\[s:)[([0-9]+(?=\])/m', $request->string, $matches);

        if (@$matches[0][0])
        {
            $source = Source::whereIn('id', $matches[0])->where('organisation_id', $organisation->id)->first();
            $request['string'] = preg_replace('/\[s:([0-9]+)\]/m', '', $request->string);
        }

        $clean = Term::cleanSearchQuery($request->string);

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
                    ],
                    /*
                    'should' => [
                        [ 'match' => [ 'status' => 'ok' ] ]
                    ]
                    */
                ]
            ],
            '_source' => [
                'user.name',
                'user.screen_name',
                'user.image',
                'user.verified',
                'text',
                'entities.medias.media',

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
                'sentiment',
                'consumer',
                'illegal',

                'display_url',
                'shortcode',

                'place'
            ]
        ];

        foreach (
            [
                [ 'consumer' => [ 'nws', 'que', 'req', 'cmp' ] ],
                [ 'sentiment' => [ 'pos', 'neg', 'neu', 'hte' ] ]
            ] as $key => $bucket)
        {
            foreach ($bucket as $key => $b)
            {
                foreach ($b as $o)
                {
                    if ($request->{$key.'_'.$o})
                    {
                        $q['query']['bool']['filter'][] = [
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

        foreach ($request->modules as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
                        $twitter_q = $q;

                        if ($request->gender != 'all')
                        {
                            $twitter_q['query']['bool']['should'][] = [ 'match' => [ 'user.gender' => $request->gender ] ];
                            $twitter_q['query']['bool']['minimum_should_match'] = 1;
                        }

                        $twitter_query = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', $twitter_q);

                        if (@$twitter_query->data['hits']['hits'])
                        {
                            $stats['hits'] = $stats['hits'] + $twitter_query->data['hits']['total'];

                            foreach ($twitter_query->data['hits']['hits'] as $object)
                            {
                                $arr = self::query_build($object);

                                $user = [
                                    'name' => $object['_source']['user']['name'],
                                    'screen_name' => $object['_source']['user']['screen_name'],
                                    'image' => $object['_source']['user']['image']
                                ];

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
                                ]);
                            }

                            $stats['counts']['twitter_tweet'] = $twitter_query->data['hits']['total'];
                        }
                    }
                break;
                case 'instagram':
                    if ($organisation->data_instagram)
                    {
                        $instagram_query = Document::search([ 'instagram', 'medias', '*' ], 'media', $q);

                        if (@$instagram_query->data['hits']['hits'])
                        {
                            $stats['hits'] = $stats['hits'] + $instagram_query->data['hits']['total'];

                            foreach ($instagram_query->data['hits']['hits'] as $object)
                            {
                                $arr = self::query_build($object);

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

                            $stats['counts']['instagram_media'] = $instagram_query->data['hits']['total'];
                        }
                    }
                break;
                case 'sozluk':
                    if ($organisation->data_sozluk)
                    {
                        $sozluk_q = $q;

                        if (@$source->source_sozluk)
                        {
                            foreach ($source->source_sozluk as $key => $id)
                            {
                                $media_q['query']['bool']['should'][] = [ 'match' => [ 'site_id' => $id ] ];
                            }

                            $media_q['query']['bool']['minimum_should_match'] = 1;
                        }

                        if ($request->gender != 'all')
                        {
                            $sozluk_q['query']['bool']['should'][] = [ 'match' => [ 'gender' => $request->gender ] ];
                            $sozluk_q['query']['bool']['minimum_should_match'] = 1;
                        }

                        $sozluk_query = Document::search([ 'sozluk', '*' ], 'entry', $sozluk_q);

                        if (@$sozluk_query->data['hits']['hits'])
                        {
                            $stats['hits'] = $stats['hits'] + $sozluk_query->data['hits']['total'];

                            foreach ($sozluk_query->data['hits']['hits'] as $object)
                            {
                                $arr = self::query_build($object);

                                $data[] = array_merge($arr, [
                                    'url' => $object['_source']['url'],
                                    'title' => $object['_source']['title'],
                                    'text' => $object['_source']['entry'],
                                    'author' => $object['_source']['author'],
                                ]);
                            }

                            $stats['counts']['sozluk_entry'] = $sozluk_query->data['hits']['total'];
                        }
                    }
                break;
                case 'news':
                    if ($organisation->data_news)
                    {
                        $media_q = $q;

                        if (@$source->source_media)
                        {
                            foreach ($source->source_media as $key => $id)
                            {
                                $media_q['query']['bool']['should'][] = [ 'match' => [ 'site_id' => $id ] ];
                            }

                            $media_q['query']['bool']['minimum_should_match'] = 1;
                        }

                        $news_query = Document::search([ 'media', 's*' ], 'article', $media_q);

                        if (@$news_query->data['hits']['hits'])
                        {
                            $stats['hits'] = $stats['hits'] + $news_query->data['hits']['total'];

                            foreach ($news_query->data['hits']['hits'] as $object)
                            {
                                $arr = self::query_build($object);

                                $arr['url'] = $object['_source']['url'];
                                $arr['title'] = $object['_source']['title'];
                                $arr['text'] = $object['_source']['description'];

                                if (@$object['_source']['image_url'])
                                {
                                    $arr['image'] = $object['_source']['image_url'];
                                }

                                $data[] = $arr;
                            }

                            $stats['counts']['media_article'] = $news_query->data['hits']['total'];
                        }
                    }
                break;
                case 'blog':
                    if ($organisation->data_blog)
                    {
                        $blog_q = $q;

                        if (@$source->source_blog)
                        {
                            foreach ($source->source_blog as $key => $id)
                            {
                                $media_q['query']['bool']['should'][] = [ 'match' => [ 'site_id' => $id ] ];
                            }

                            $media_q['query']['bool']['minimum_should_match'] = 1;
                        }

                        $blog_query = Document::search([ 'blog', 's*' ], 'document', $blog_q);

                        if (@$blog_query->data['hits']['hits'])
                        {
                            $stats['hits'] = $stats['hits'] + $blog_query->data['hits']['total'];

                            foreach ($blog_query->data['hits']['hits'] as $object)
                            {
                                $arr = self::query_build($object);

                                $arr['url'] = $object['_source']['url'];
                                $arr['title'] = $object['_source']['title'];
                                $arr['text'] = $object['_source']['description'];

                                if (@$object['_source']['image_url'])
                                {
                                    $arr['image'] = $object['_source']['image_url'];
                                }

                                $data[] = $arr;
                            }

                            $stats['counts']['blog_document'] = $blog_query->data['hits']['total'];
                        }
                    }
                break;
                case 'youtube_video':
                    if ($organisation->data_youtube_video)
                    {
                        $youtube_video_query = Document::search([ 'youtube', 'videos' ], 'video', $q);

                        if (@$youtube_video_query->data['hits']['hits'])
                        {
                            $stats['hits'] = $stats['hits'] + $youtube_video_query->data['hits']['total'];

                            foreach ($youtube_video_query->data['hits']['hits'] as $object)
                            {
                                $arr = self::query_build($object);

                                $data[] = array_merge($arr, [
                                    'title' => $object['_source']['title'],
                                    'text' => @$object['_source']['description'],
                                    'channel' => [
                                        'id' => $object['_source']['channel']['id'],
                                        'title' => $object['_source']['channel']['title']
                                    ],
                                ]);
                            }

                            $stats['counts']['youtube_video'] = $youtube_video_query->data['hits']['total'];
                        }
                    }
                break;
                case 'youtube_comment':
                    if ($organisation->data_youtube_comment)
                    {
                        $youtube_comment_query = Document::search([ 'youtube', 'comments', '*' ], 'comment', $q);

                        if (@$youtube_comment_query->data['hits']['hits'])
                        {
                            $stats['hits'] = $stats['hits'] + $youtube_comment_query->data['hits']['total'];

                            foreach ($youtube_comment_query->data['hits']['hits'] as $object)
                            {
                                $arr = self::query_build($object);

                                $data[] = array_merge($arr, [
                                    'video_id' => $object['_source']['video_id'],
                                    'channel' => [
                                        'id' => $object['_source']['channel']['id'],
                                        'title' => $object['_source']['channel']['title']
                                    ],
                                    'text' => $object['_source']['text'],
                                ]);
                            }

                            $stats['counts']['youtube_comment'] = $youtube_comment_query->data['hits']['total'];
                        }
                    }
                break;
                case 'shopping':
                    if ($organisation->data_shopping)
                    {
                        $shopping_q = $q;

                        if (@$source->shopping_q)
                        {
                            foreach ($source->shopping_q as $key => $id)
                            {
                                $media_q['query']['bool']['should'][] = [ 'match' => [ 'site_id' => $id ] ];
                            }

                            $media_q['query']['bool']['minimum_should_match'] = 1;
                        }

                        $shopping_query = Document::search([ 'shopping', '*' ], 'product', $shopping_q);

                        if (@$shopping_query->data['hits']['hits'])
                        {
                            $stats['hits'] = $stats['hits'] + $shopping_query->data['hits']['total'];

                            foreach ($shopping_query->data['hits']['hits'] as $object)
                            {
                                $arr = self::query_build($object);

                                if (@$object['_source']['description'])
                                {
                                    $arr['text'] = $object['_source']['description'];
                                }

                                $data[] = array_merge($arr, [
                                    'url' => $object['_source']['url'],
                                    'title' => $object['_source']['title'],
                                ]);
                            }

                            $stats['counts']['shopping'] = $shopping_query->data['hits']['total'];
                        }
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

        usort($data, '\App\Utilities\DateUtility::dateSort');

        return [
            'status' => 'ok',
            'hits' => $data,
            'words' => $clean->words,
            'stats' => $stats
        ];
    }

    /**
     * Arama Sonuçları
     *
     * @return array
     */
    public static function aggregation(ArchiveAggregationRequest $request)
    {
        $data = [];

        $clean = Term::cleanSearchQuery($request->string);

        $q = [
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
            'size' => 0
        ];

        $modules = [];

        $organisation = auth()->user()->organisation;

        foreach (
            [
                [ 'consumer' => [ 'nws', 'que', 'req', 'cmp' ] ],
                [ 'sentiment' => [ 'pos', 'neg', 'neu', 'hte' ] ]
            ] as $key => $bucket)
        {
            foreach ($bucket as $key => $b)
            {
                foreach ($b as $o)
                {
                    if ($request->{$key.'_'.$o})
                    {
                        $q['query']['bool']['filter'][] = [ 'range' => [ implode('.', [ $key, $o ]) => [ 'gte' => implode('.', [ 0, $request->{$key.'_'.$o} ]) ] ] ];
                    }
                }
            }
        }

        foreach ($request->modules as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
                        if ($request->gender != 'all')
                        {
                            $q['query']['bool']['should'][] = [ 'match' => [ 'user.gender' => $request->gender ] ];
                            $q['query']['bool']['minimum_should_match'] = 1;
                        }

                        $modules[] = 'tweet';
                    }
                break;
                case 'instagram':
                    if ($organisation->data_twitter)
                    {
                        $modules[] = 'media';
                    }
                break;
                case 'sozluk':
                    if ($organisation->data_sozluk)
                    {
                        if ($request->gender != 'all')
                        {
                            $q['query']['bool']['should'][] = [ 'match' => [ 'gender' => $request->gender ] ];
                            $q['query']['bool']['minimum_should_match'] = 1;
                        }

                        $modules[] = 'entry';
                    }
                break;
                case 'news':
                    if ($organisation->data_news)
                    {
                        $modules[] = 'article';
                    }
                break;
                case 'blog':
                    if ($organisation->data_blog)
                    {
                        $modules[] = 'document';
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
            case 'sentiment':
                $aggs['sentiment_pos'] = [ 'sum' => [ 'field' => 'sentiment.pos' ] ];
                $aggs['sentiment_neg'] = [ 'sum' => [ 'field' => 'sentiment.neg' ] ];
                $aggs['sentiment_neu'] = [ 'sum' => [ 'field' => 'sentiment.neu' ] ];
                $aggs['sentiment_hte'] = [ 'sum' => [ 'field' => 'sentiment.hte' ] ];
            break;
            case 'consumer':
                $aggs['consumer_que'] = [ 'sum' => [ 'field' => 'consumer.req' ] ];
                $aggs['consumer_req'] = [ 'sum' => [ 'field' => 'consumer.que' ] ];
                $aggs['consumer_cmp'] = [ 'sum' => [ 'field' => 'consumer.nws' ] ];
                $aggs['consumer_nws'] = [ 'sum' => [ 'field' => 'consumer.cmp' ] ];
            break;
            case 'gender':
                $aggs['twitter'] = [ 'terms' => [ 'field' => 'user.gender' ] ];
                $aggs['sozluk'] = [ 'terms' => [ 'field' => 'gender' ] ];
                $aggs['youtube'] = [ 'terms' => [ 'field' => 'channel.gender' ] ];
                $aggs['shopping'] = [ 'terms' => [ 'field' => 'seller.gender' ] ];
            break;
            case 'hashtag':
                $aggs['hashtag'] = [
                    'nested' => [ 'path' => 'entities.hashtags' ],
                    'aggs' => [
                        'hits' => [
                            'terms' => [
                                'field' => 'entities.hashtags.hashtag',
                                'size' => 10
                            ]
                        ]
                    ]
                ];
            break;
            case 'mention':
                $aggs['twitter_users'] = [
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
                ];
                $aggs['twitter_mentions'] = [
                    'nested' => [ 'path' => 'entities.mentions' ],
                    'aggs' => [
                        'hits' => [
                            'terms' => [
                                'field' => 'entities.mentions.mention.id',
                                'size' => 10
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
                ];
                $aggs['youtube_users'] = [
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
                ];
                $aggs['sozluk_users'] = [
                    'terms' => [
                        'field' => 'author',
                        'size' => 10
                    ]
                ];
                $aggs['shopping_users'] = [
                    'terms' => [
                        'field' => 'seller.name',
                        'size' => 10
                    ]
                ];
            break;
            case 'platform':
                $aggs['platform'] = [
                    'terms' => [
                        'field' => 'platform',
                        'size' => 10
                    ]
                ];
            break;
            case 'place':
                $aggs['place'] = [
                    'terms' => [
                        'field' => 'place.name'
                    ]
                ];
            break;
            case 'histogram':
                $aggs['daily'] = [
                    'histogram' => [
                        'script' => 'doc.created_at.value.getDayOfWeek()',
                        'interval' => 1
                    ]
                ];
                $aggs['hourly'] = [
                    'histogram' => [
                        'script' => 'doc.created_at.value.getHourOfDay()',
                        'interval' => 1
                    ]
                ];
            break;
        }

        $query = [
            'aggs' => Document::search([ '*' ], implode(',', $modules), array_merge($q, [ 'size' => 0, 'aggs' => $aggs ]))
        ];

        $data = [
            'status' => 'err'
        ];

        switch ($request->type)
        {
            case 'sentiment':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations'])
                    {
                        $clean_hits = $query['aggs']->data['aggregations'];
                        $total_hits = $clean_hits['sentiment_hte']['value'] + $clean_hits['sentiment_neg']['value'] + $clean_hits['sentiment_neu']['value'] + $clean_hits['sentiment_hte']['value'];

                        $data['data'] = [
                            'pos' => round($clean_hits['sentiment_hte']['value'] ? $clean_hits['sentiment_hte']['value']*100/$total_hits : 0, 2),
                            'neg' => round($clean_hits['sentiment_neg']['value'] ? $clean_hits['sentiment_neg']['value']*100/$total_hits : 0, 2),
                            'neu' => round($clean_hits['sentiment_neu']['value'] ? $clean_hits['sentiment_neu']['value']*100/$total_hits : 0, 2),
                            'hte' => round($clean_hits['sentiment_hte']['value'] ? $clean_hits['sentiment_hte']['value']*100/$total_hits : 0, 2)
                        ];
                    }
                }
            break;
            case 'consumer':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations'])
                    {
                        $clean_hits = $query['aggs']->data['aggregations'];
                        $total_hits = $clean_hits['consumer_req']['value'] + $clean_hits['consumer_que']['value'] + $clean_hits['consumer_cmp']['value'] + $clean_hits['consumer_nws']['value'];

                        $data['data'] = [
                            'que' => round($clean_hits['consumer_que']['value'] ? $clean_hits['consumer_que']['value']*100/$total_hits : 0, 2),
                            'req' => round($clean_hits['consumer_req']['value'] ? $clean_hits['consumer_req']['value']*100/$total_hits : 0, 2),
                            'cmp' => round($clean_hits['consumer_cmp']['value'] ? $clean_hits['consumer_cmp']['value']*100/$total_hits : 0, 2),
                            'nws' => round($clean_hits['consumer_nws']['value'] ? $clean_hits['consumer_nws']['value']*100/$total_hits : 0, 2)
                        ];

                        foreach ([ 'que', 'req', 'cmp', 'nws' ] as $key => $item)
                        {
                            if ($data['data'][$item] >= 25)
                            {
                                $data['data'][$item] = $data['data'][$item]-25;
                            }
                            else
                            {
                                $data['data'][$item] = 0;
                            }
                        }
                    }
                }
            break;
            case 'gender':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations'])
                    {
                        $data['data']['male'] = 0;
                        $data['data']['female'] = 0;
                        $data['data']['unknown'] = 0;

                        foreach ([ 'twitter', 'sozluk', 'youtube', 'shopping' ] as $item)
                        {
                            $clean_hits = $query['aggs']->data['aggregations'][$item]['buckets'];

                            if ($clean_hits)
                            {
                                foreach ($clean_hits as $bucket)
                                {
                                    switch ($bucket['key'])
                                    {
                                        case 'male':
                                            $data['data']['male'] = $data['data']['male'] + $bucket['doc_count'];
                                        break;
                                        case 'female':
                                            $data['data']['female'] = $data['data']['female'] + $bucket['doc_count'];
                                        break;
                                        case 'unknown':
                                            $data['data']['unknown'] = $data['data']['unknown'] + $bucket['doc_count'];
                                        break;
                                    }
                                }
                            }
                        }

                        $total_hits = $data['data']['male'] + $data['data']['female'] + $data['data']['unknown'];

                        $data['data']['male'] = round(($data['data']['male'] ? $data['data']['male']*100/$total_hits : 0), 2);
                        $data['data']['female'] = round(($data['data']['female'] ? $data['data']['female']*100/$total_hits : 0), 2);
                        $data['data']['unknown'] = round(($data['data']['unknown'] ? $data['data']['unknown']*100/$total_hits : 0), 2);
                    }
                }
            break;
            case 'hashtag':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations'])
                    {
                        if ($query['aggs']->data['aggregations']['hashtag']['doc_count'])
                        {
                            foreach ($query['aggs']->data['aggregations']['hashtag']['hits']['buckets'] as $key => $row)
                            {
                                $data['data'][$row['key']] = $row['doc_count'];
                            }
                        }
                    }
                }
            break;
            case 'mention':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations'])
                    {
                        if (count($query['aggs']->data['aggregations']['twitter_users']['buckets']))
                        {
                            foreach ($query['aggs']->data['aggregations']['twitter_users']['buckets'] as $key => $row)
                            {
                                if (@$row['properties']['hits']['hits'][0]['_source']['user'])
                                {
                                    $data['data']['twitter_users'][$row['key']] = array_merge(
                                        $row['properties']['hits']['hits'][0]['_source']['user'],
                                        [ 'hit' => $row['doc_count'] ]
                                    );
                                }
                            }
                        }

                        if ($query['aggs']->data['aggregations']['twitter_mentions']['doc_count'])
                        {
                            foreach ($query['aggs']->data['aggregations']['twitter_mentions']['hits']['buckets'] as $key => $row)
                            {
                                $data['data']['twitter_mentions'][$row['key']] = array_merge(
                                    $row['properties']['hits']['hits'][0]['_source']['mention'],
                                    [ 'hit' => $row['doc_count'] ]
                                );
                            }
                        }

                        if (count($query['aggs']->data['aggregations']['youtube_users']['buckets']))
                        {
                            foreach ($query['aggs']->data['aggregations']['youtube_users']['buckets'] as $key => $row)
                            {
                                $data['data']['youtube_users'][$row['key']] = array_merge(
                                    $row['properties']['hits']['hits'][0]['_source']['channel'],
                                    [ 'hit' => $row['doc_count'] ]
                                );
                            }
                        }

                        if (count($query['aggs']->data['aggregations']['sozluk_users']['buckets']))
                        {
                            foreach ($query['aggs']->data['aggregations']['sozluk_users']['buckets'] as $key => $row)
                            {
                                $data['data']['sozluk_users'][] = [
                                    'name' => $row['key'],
                                    'hit' => $row['doc_count']
                                ];
                            }
                        }

                        if (count($query['aggs']->data['aggregations']['shopping_users']['buckets']))
                        {
                            foreach ($query['aggs']->data['aggregations']['shopping_users']['buckets'] as $key => $row)
                            {
                                $data['data']['shopping_users'][] = [
                                    'name' => $row['key'],
                                    'hit' => $row['doc_count']
                                ];
                            }
                        }
                    }
                }
            break;
            case 'platform':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations']['platform']['buckets'])
                    {
                        foreach ($query['aggs']->data['aggregations']['platform']['buckets'] as $key => $row)
                        {
                            $data['data']['platform'][] = [
                                'name' => $row['key'],
                                'hit' => $row['doc_count']
                            ];
                        }
                    }
                }
            break;
            case 'place':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations']['place']['buckets'])
                    {
                        foreach ($query['aggs']->data['aggregations']['place']['buckets'] as $key => $row)
                        {
                            $data['data']['place'][] = [
                                'name' => $row['key'],
                                'hit' => $row['doc_count']
                            ];
                        }
                    }
                }
            break;
            case 'histogram':
                if ($query['aggs']->status == 'ok')
                {
                    $data['status'] = 'ok';

                    if (@$query['aggs']->data['aggregations']['daily']['buckets'])
                    {
                        $data['data']['daily'] = $query['aggs']->data['aggregations']['daily']['buckets'];
                        $data['data']['hourly'] = $query['aggs']->data['aggregations']['hourly']['buckets'];
                    }
                }
            break;
        }

        return $data;
    }
}
