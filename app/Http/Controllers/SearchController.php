<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis as RedisCache;

use App\Http\Controllers\Controller;

use App\Http\Requests\Search\ArchiveRequest;
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
            'category'
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
        $sources = Source::where('organisation_id', $organisation->id)->get();

        $trends = json_decode(RedisCache::get(implode(':', [ config('system.db.alias'), 'trends', 'twitter_hashtag' ])));

        return view('search', compact('q', 's', 'e', 'trends', 'organisation', 'sources'));
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

        $data = [];

        foreach ($request->modules as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
                        $twitter_q = $q;

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

                        $sozluk_data = self::sozluk($request, $sozluk_q, @$source->source_sozluk);

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

                        $news_data = self::news($request, $news_q, @$source->source_media);

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

                        $blog_data = self::blog($request, $blog_q, @$source->source_blog);

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

                        $shopping_data = self::shopping($request, $shopping_q, @$source->source_shopping);

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

        usort($data, '\App\Utilities\DateUtility::dateSort');

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

    public static function sozluk($search, array $q, $source = null)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        if (@$source)
        {
            foreach ($source as $key => $id)
            {
                $q['query']['bool']['should'][] = [ 'match' => [ 'site_id' => $id ] ];
            }

            $q['query']['bool']['minimum_should_match'] = 1;
        }

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

    public static function news($search, array $q, $source = null)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

        if ($source)
        {
            foreach ($source as $key => $id)
            {
                $q['query']['bool']['should'][] = [ 'match' => [ 'site_id' => $id ] ];
            }

            $q['query']['bool']['minimum_should_match'] = 1;
        }

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

    public static function blog($search, array $q, $source = null)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

        if (@$source)
        {
            foreach ($source as $key => $id)
            {
                $media_q['query']['bool']['should'][] = [ 'match' => [ 'site_id' => $id ] ];
            }

            $media_q['query']['bool']['minimum_should_match'] = 1;
        }

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

    public static function shopping($search, array $q, $source = null)
    {
        $aggs = [];
        $data = [];
        $stats = [ 'total' => 0 ];

        $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

        if (@$source)
        {
            foreach ($source as $key => $id)
            {
                $media_q['query']['bool']['should'][] = [ 'match' => [ 'site_id' => $id ] ];
            }

            $media_q['query']['bool']['minimum_should_match'] = 1;
        }

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
