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
        $request['categories'] = json_encode($request->categories);

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
            'categories'
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
    private static function result_default(array $object)
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

                        if ($request->sharp)
                        {
                            $twitter_q['query']['bool']['must_not'][] = [ 'match' => [ 'external.type' => 'retweet' ] ];
                            $twitter_q['query']['bool']['must'][] = [ 'range' => [ 'counts.hashtag' => [ 'lte' => 2 ] ] ];
                            $twitter_q['query']['bool']['must'][] = [ 'range' => [ 'illegal.nud' => [ 'lte' => 0.4 ] ] ];
                            $twitter_q['query']['bool']['must'][] = [ 'range' => [ 'illegal.bet' => [ 'lte' => 0.4 ] ] ];
                        }

                        $twitter_query = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', $twitter_q);

                        if (@$twitter_query->data['hits']['hits'])
                        {
                            $stats['hits'] = $stats['hits'] + $twitter_query->data['hits']['total'];

                            foreach ($twitter_query->data['hits']['hits'] as $object)
                            {
                                $arr = self::result_default($object);

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
                                $arr = self::result_default($object);

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

                        $media_q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

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

                            $stats['counts']['media_article'] = $news_query->data['hits']['total'];
                        }
                    }
                break;
                case 'blog':
                    if ($organisation->data_blog)
                    {
                        $blog_q = $q;

                        $blog_q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

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

                            $stats['counts']['youtube_comment'] = $youtube_comment_query->data['hits']['total'];
                        }
                    }
                break;
                case 'shopping':
                    if ($organisation->data_shopping)
                    {
                        $shopping_q = $q;

                        $shopping_q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];

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

                            $stats['counts']['shopping_product'] = $shopping_query->data['hits']['total'];
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
}
