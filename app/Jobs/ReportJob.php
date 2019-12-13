<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Report;
use App\Models\ReportPage;
use App\Models\Alarm;

use App\Models\Crawlers\SozlukCrawler;
use App\Models\Crawlers\ShoppingCrawler;
use App\Models\Crawlers\MediaCrawler;
use App\Models\Crawlers\BlogCrawler;

use App\Http\Controllers\SearchController;

use Term;

use App\SMS;

use App\Elasticsearch\Document;

use App\Models\User\User;
use App\Models\SavedSearch;
use App\Models\Link;

use App\Notifications\AlarmReportNotification;

use Carbon\Carbon;

class ReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $alarm;
    private $report;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Alarm $alarm = null, Report $report = null)
    {
        $this->alarm = $alarm;
        $this->report = $report;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->report)
        {
            $search = new SavedSearch;
            $search->name = $this->report->name;
            $search->string = $this->report->subject;
            $search->modules = array_keys(config('system.modules'));

            $interval = Carbon::parse($this->report->date_1)->diffInMinutes($this->report->date_2);

            $organisation = $this->report->organisation;
        }
        else
        {
            $search = $this->alarm->search;
            $interval = $this->alarm->interval;

            $organisation = $search->organisation;
        }

        $clean = Term::cleanSearchQuery($search->string);

        $q = [
            'from' => 0,
            'size' => 3,
            'sort' => [ 'created_at' => 'asc' ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd HH',
                                    'gte' => date('Y-m-d H', strtotime('-'.$interval.' minutes'))
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
            'aggs' => [
                'hourly' =>[
                    'histogram' => [
                        'script' => 'doc.created_at.value.getHourOfDay()',
                        'interval' => 1
                    ]
                ],
                'pos' => [ 'filter' => [ 'range' => [ 'sentiment.pos' => [ 'gte' => 0.5 ] ] ] ],
                'neu' => [ 'filter' => [ 'range' => [ 'sentiment.neu' => [ 'gte' => 0.5 ] ] ] ],
                'neg' => [ 'filter' => [ 'range' => [ 'sentiment.neg' => [ 'gte' => 0.5 ] ] ] ],
                'hte' => [ 'filter' => [ 'range' => [ 'sentiment.hte' => [ 'gte' => 0.5 ] ] ] ],

                'nws' => [ 'filter' => [ 'range' => [ 'consumer.nws' => [ 'gte' => 0.5 ] ] ] ],
                'req' => [ 'filter' => [ 'range' => [ 'consumer.req' => [ 'gte' => 0.5 ] ] ] ],
                'que' => [ 'filter' => [ 'range' => [ 'consumer.que' => [ 'gte' => 0.5 ] ] ] ],
                'cmp' => [ 'filter' => [ 'range' => [ 'consumer.cmp' => [ 'gte' => 0.5 ] ] ] ],

                'category' => [ 'terms' => [ 'field' => 'category', 'size' => 100 ] ]
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

        $stats = [
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
        $histogram = [];
        $place = [];
        $platform = [];
        $sentiment = [];
        $consumer = [];
        $gender = [];
        $local_press = [];
        $category = [];
        $hashtag = [];

        $raw_datas = [];
        $first_datas = [];
        $twitter_favorite_datas = [];
        $twitter_retweet_datas = [];

        $pages = [];
        $sort = 1;

        $starttime = explode(' ', microtime());
        $starttime = $starttime[1] + $starttime[0];

        foreach ($search->modules as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
                        $twitter_fav_q = $q;
                        $twitter_fav_q['sort'] = [ 'counts.favorite' => 'desc' ];

                        $twitter_fav_data = SearchController::tweet($search, $twitter_fav_q, true);

                        if (count($twitter_fav_data['data']))
                        {
                            foreach ($twitter_fav_data['data'] as $item)
                            {
                                $original = self::originalTweet($item); if ($original) $item['_source']['original'] = $original;

                                $twitter_favorite_datas[] = $item['_source'];
                            }
                        }

                        $twitter_verified_q = $q;
                        $twitter_verified_q['query']['bool']['must'][] = [ 'match' => [ 'user.verified' => true ] ];
                        $twitter_verified_q['aggs']['hit_users'] = [
                            'terms' => [
                                'field' => 'user.id',
                                'size' => 100
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
                        $twitter_verified_data = SearchController::tweet($search, $twitter_verified_q, true);

                        $twitter_q = $q;

                        $twitter_q['sort'] = [ 'counts.retweet' => 'desc' ];
                        $twitter_q['aggs']['mentions'] = [ 'nested' => [ 'path' => 'entities.mentions' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.mentions.mention.id' ] ] ] ];
                        $twitter_q['aggs']['hashtags'] = [ 'nested' => [ 'path' => 'entities.hashtags' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.hashtags.hashtag' ] ] ] ];
                        $twitter_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'user.id' ] ];
                        $twitter_q['aggs']['verified_users'] = [ 'filter' => [ 'exists' => [ 'field' => 'user.verified' ] ] ];
                        $twitter_q['aggs']['followers'] = [ 'avg' => [ 'field' => 'user.counts.followers' ] ];
                        $twitter_q['aggs']['reach'] = [ 'terms' => [ 'field' => 'external.id' ] ];
                        $twitter_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];
                        $twitter_q['aggs']['place'] = [ 'terms' => [ 'field' => 'place.name', 'size' => 15 ] ];
                        $twitter_q['aggs']['platform'] = [ 'terms' => [ 'field' => 'platform', 'size' => 10 ] ];
                        $twitter_q['aggs']['gender'] = [ 'terms' => [ 'field' => 'user.gender' ] ];
                        $twitter_q['aggs']['category'] = [ 'terms' => [ 'field' => 'category', 'size' => 100 ] ];
                        $twitter_q['aggs']['hashtag'] = [ 'nested' => [ 'path' => 'entities.hashtags' ], 'aggs' => [ 'hits' => [ 'terms' => [ 'field' => 'entities.hashtags.hashtag', 'size' => 25 ] ] ] ];
                        $twitter_q['aggs']['influencers'] = [
                            'terms' => [
                                'field' => 'user.id',
                                'size' => 100
                            ],
                            'aggs' => [
                                'properties' => [
                                    'top_hits' => [
                                        'size' => 1,
                                        '_source' => [
                                            'include' => [
                                                'user.name',
                                                'user.screen_name',
                                                'user.counts.followers'
                                            ]
                                        ]
                                    ]
                                ],
                                'total_followers' => [
                                    'avg' => [
                                        'field' => 'user.counts.followers'
                                    ]
                                ],
                                'followers_bucket_sort' => [
                                    'bucket_sort' => [
                                        'sort' => [
                                            [ 'total_followers' => [ 'order' => 'desc' ] ]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                        $twitter_q['aggs']['hit_mentions'] = [
                            'nested' => [ 'path' => 'entities.mentions' ],
                            'aggs' => [
                                'hits' => [
                                    'terms' => [
                                        'field' => 'entities.mentions.mention.id',
                                        'size' => 100
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
                        $twitter_q['aggs']['hit_users'] = [
                            'terms' => [
                                'field' => 'user.id',
                                'size' => 100
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

                        $tweet_data = SearchController::tweet($search, $twitter_q, true);

                        if (count($tweet_data['data']))
                        {
                            foreach ($tweet_data['data'] as $item)
                            {
                                $original = self::originalTweet($item); if ($original) $item['_source']['original'] = $original;

                                $twitter_retweet_datas[] = $item['_source'];
                            }

                            $_histogram = $this->histogram('Twitter', $tweet_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                            $_sentiment = $this->sentiment('Twitter', $tweet_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                            $_consumer = $this->consumer('Twitter', $tweet_data['aggs']); if ($_consumer) $consumer[] = $_consumer;
                            $_gender = $tweet_data['aggs']['gender']; if ($_gender) $gender['twitter']['gender'] = $_gender;
                            $_place = $this->place('Twitter', $tweet_data['aggs']); if ($_place) $place['twitter'] = $_place;
                            $_platform = $this->platform('Twitter', $tweet_data['aggs']); if ($_platform) $platform['twitter'] = $_platform;
                            $_category = $tweet_data['aggs']['category']['buckets']; if ($_category) $category[] = $_category;
                            $_hashtag = $tweet_data['aggs']['hashtag']['hits']['buckets']; if ($_hashtag) $hashtag['twitter'] = $_hashtag;

                            if (count($tweet_data['aggs']['hit_users']['buckets']))
                            {
                                $raw_datas[] = [
                                    'title' => 'Twitter Kullanıcıları / En Çok Tweet',
                                    'subtitle' => 'Konu hakkında en çok Tweet paylaşan Twitter kullanıcıları.',
                                    'data' => $tweet_data['aggs']['hit_users']['buckets'],
                                    'type' => 'data.twitterUsers'
                                ];
                            }

                            if (count($twitter_verified_data['aggs']['hit_users']['buckets']))
                            {
                                $raw_datas[] = [
                                    'title' => 'Twitter Kullanıcıları / Tanınmış Hesaplar',
                                    'subtitle' => 'Konu hakkında en çok Tweet paylaşan tanınmış Twitter kullanıcıları.',
                                    'data' => $twitter_verified_data['aggs']['hit_users']['buckets'],
                                    'type' => 'data.twitterUsers'
                                ];
                            }

                            if (count($tweet_data['aggs']['influencers']['buckets']))
                            {
                                $raw_datas[] = [
                                    'title' => 'Twitter Kullanıcıları / Takipçi Sayısına Göre',
                                    'subtitle' => 'Konuya dahil olmuş yüksek takipçili Twitter kullanıcıları.',
                                    'data' => $tweet_data['aggs']['influencers']['buckets'],
                                    'type' => 'data.twitterInfluencers'
                                ];
                            }

                            if ($tweet_data['aggs']['hit_mentions']['doc_count'])
                            {
                                $raw_datas[] = [
                                    'title' => 'Twitter Kullanıcıları / Konuşulanlar',
                                    'subtitle' => 'Konu içerisinde en çok bahsedilen Twitter kullanıcıları.',
                                    'data' => $tweet_data['aggs']['hit_mentions']['hits']['buckets'],
                                    'type' => 'data.twitterMentions'
                                ];
                            }

                            $stats['twitter']['mentions'] = @$tweet_data['aggs']['mentions']['doc_count'];
                            $stats['twitter']['hashtags'] = @$tweet_data['aggs']['hashtags']['doc_count'];
                            $stats['twitter']['unique_users'] = @$tweet_data['aggs']['unique_users']['value'];
                            $stats['twitter']['verified_users'] = @$tweet_data['aggs']['verified_users']['doc_count'];
                            $stats['twitter']['followers'] = @$tweet_data['aggs']['followers']['value'];
                            $stats['twitter']['reach'] = @$tweet_data['aggs']['reach']['sum_other_doc_count'];

                            $stats['hits'] = $stats['hits'] + $tweet_data['aggs']['total']['value'];
                            $stats['counts']['twitter_tweet'] = $tweet_data['aggs']['total']['value'];
                        }
                    }
                break;
                case 'instagram':
                    if ($organisation->data_instagram)
                    {
                        $instagram_q = $q;

                        $instagram_q['aggs']['mentions'] = [ 'nested' => [ 'path' => 'entities.mentions' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.mentions.mention.id' ] ] ] ];
                        $instagram_q['aggs']['hashtags'] = [ 'nested' => [ 'path' => 'entities.hashtags' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.hashtags.hashtag' ] ] ] ];
                        $instagram_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'user.id' ] ];
                        $instagram_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];
                        $instagram_q['aggs']['place'] = [ 'terms' => [ 'field' => 'place.name', 'size' => 15 ] ];
                        $instagram_q['aggs']['category'] = [ 'terms' => [ 'field' => 'category', 'size' => 100 ] ];
                        $instagram_q['aggs']['hashtag'] = [ 'nested' => [ 'path' => 'entities.hashtags' ], 'aggs' => [ 'hits' => [ 'terms' => [ 'field' => 'entities.hashtags.hashtag', 'size' => 25 ] ] ] ];

                        $instagram_data = SearchController::instagram($search, $instagram_q, true);

                        if (count($instagram_data['data']))
                        {
                            $_histogram = $this->histogram('Instagram', $instagram_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                            $_sentiment = $this->sentiment('Instagram', $instagram_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                            $_consumer = $this->consumer('Instagram', $instagram_data['aggs']); if ($_consumer) $consumer[] = $_consumer;
                            $_category = $instagram_data['aggs']['category']['buckets']; if ($_category) $category[] = $_category;
                            $_hashtag = $instagram_data['aggs']['hashtag']['hits']['buckets']; if ($_hashtag) $hashtag['instagram'] = $_hashtag;

                            $stats['instagram']['mentions'] = @$instagram_data['aggs']['mentions']['doc_count'];
                            $stats['instagram']['hashtags'] = @$instagram_data['aggs']['hashtags']['doc_count'];
                            $stats['instagram']['unique_users'] = @$instagram_data['aggs']['unique_users']['value'];

                            $stats['hits'] = $stats['hits'] + $instagram_data['aggs']['total']['value'];
                            $stats['counts']['instagram_media'] = $instagram_data['aggs']['total']['value'];
                        }
                    }
                break;
                case 'sozluk':
                    if ($organisation->data_sozluk)
                    {
                        $sozluk_q = $q;

                        $sozluk_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'author' ] ];
                        $sozluk_q['aggs']['unique_topics'] = [ 'cardinality' => [ 'field' => 'group_name' ] ];
                        $sozluk_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                        $sozluk_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];
                        $sozluk_q['aggs']['gender'] = [ 'terms' => [ 'field' => 'gender' ] ];
                        $sozluk_q['aggs']['category'] = [ 'terms' => [ 'field' => 'category', 'size' => 100 ] ];
                        $sozluk_q['aggs']['hit_sites'] = [
                            'terms' => [
                                'field' => 'site_id',
                                'size' => 100
                            ]
                        ];
                        $sozluk_q['aggs']['hit_topics'] = [
                            'terms' => [
                                'field' => 'group_name',
                                'size' => 100
                            ],
                            'aggs' => [
                                'properties' => [
                                    'top_hits' => [
                                        'size' => 1,
                                        '_source' => [
                                            'include' => [
                                                'title',
                                                'site_id'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                        $sozluk_q['aggs']['hit_users'] = [
                            'terms' => [
                                'field' => 'author',
                                'size' => 100
                            ],
                            'aggs' => [
                                'properties' => [
                                    'top_hits' => [
                                        'size' => 1,
                                        '_source' => [
                                            'include' => [
                                                'site_id'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ];

                        $sozluk_data = SearchController::sozluk($search, $sozluk_q, true);

                        if (count($sozluk_data['data']))
                        {
                            $_histogram = $this->histogram('Sözlük', $sozluk_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                            $_sentiment = $this->sentiment('Sözlük', $sozluk_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                            $_consumer = $this->consumer('Sözlük', $sozluk_data['aggs']); if ($_consumer) $consumer[] = $_consumer;
                            $_gender = $sozluk_data['aggs']['gender']; if ($_gender) $gender['sozluk']['gender'] = $_gender;
                            $_category = $sozluk_data['aggs']['category']['buckets']; if ($_category) $category[] = $_category;

                            $stats['sozluk']['unique_users'] = @$sozluk_data['aggs']['unique_users']['value'];
                            $stats['sozluk']['unique_topics'] = @$sozluk_data['aggs']['unique_topics']['value'];
                            $stats['sozluk']['unique_sites'] = @$sozluk_data['aggs']['unique_sites']['value'];

                            if (count($sozluk_data['aggs']['hit_sites']['buckets']))
                            {
                                $_items = array_map(function($item) {
                                    return [
                                        'hit' => $item['doc_count'],
                                        'name' => @SozlukCrawler::where('id', $item['key'])->value('name')
                                    ];
                                }, $sozluk_data['aggs']['hit_sites']['buckets']);

                                $raw_datas[] = [
                                    'title' => 'Sözlükler',
                                    'subtitle' => 'Konu hakkında entry girilen sözlükler.',
                                    'data' => $_items,
                                    'type' => 'data.sozlukSites'
                                ];
                            }

                            if (count($sozluk_data['aggs']['hit_topics']['buckets']))
                            {
                                $_items = array_map(function($item) {
                                    return [
                                        'hit' => $item['doc_count'],
                                        'site' => @SozlukCrawler::where('id', $item['properties']['hits']['hits'][0]['_source']['site_id'])->value('name'),
                                        'title' => $item['properties']['hits']['hits'][0]['_source']['title']
                                    ];
                                }, $sozluk_data['aggs']['hit_topics']['buckets']);

                                $raw_datas[] = [
                                    'title' => 'Sözlük Başlıkları',
                                    'subtitle' => 'Konu hakkında entry girilen sözlük başlıkları.',
                                    'data' => $_items,
                                    'type' => 'data.sozlukTopics'
                                ];
                            }

                            if (count($sozluk_data['aggs']['hit_users']['buckets']))
                            {
                                $_items = array_map(function($item) {
                                    return [
                                        'hit' => $item['doc_count'],
                                        'site' => @SozlukCrawler::where('id', $item['properties']['hits']['hits'][0]['_source']['site_id'])->value('name'),
                                        'name' => $item['key']
                                    ];
                                }, $sozluk_data['aggs']['hit_users']['buckets']);

                                $raw_datas[] = [
                                    'title' => 'Sözlük Yazarları',
                                    'subtitle' => 'Konu hakkında en çok entry giren sözlük yazarları.',
                                    'data' => $_items,
                                    'type' => 'data.sozlukUsers'
                                ];
                            }

                            $stats['hits'] = $stats['hits'] + $sozluk_data['aggs']['total']['value'];
                            $stats['counts']['sozluk_entry'] = $sozluk_data['aggs']['total']['value'];

                            foreach ($sozluk_data['data'] as $item)
                            {
                                $first_datas[$item['_type']][] = $item['_source'];
                            }
                        }
                    }
                break;
                case 'news':
                    if ($organisation->data_news)
                    {
                        $news_q = $q;

                        unset($news_q['aggs']['nws']);
                        unset($news_q['aggs']['req']);
                        unset($news_q['aggs']['que']);
                        unset($news_q['aggs']['cmp']);

                        $news_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                        $news_q['aggs']['local_states'] = [ 'cardinality' => [ 'field' => 'state' ] ];
                        $news_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];
                        $news_q['aggs']['local_press'] = [ 'terms' => [ 'field' => 'state', 'size' => 100 ] ];
                        $news_q['aggs']['category'] = [ 'terms' => [ 'field' => 'category', 'size' => 100 ] ];
                        $news_q['aggs']['hit_sites'] = [
                            'terms' => [
                                'field' => 'site_id',
                                'size' => 100
                            ]
                        ];

                        if ($search->state)
                        {
                            $news_q['query']['bool']['must'][] = [ 'match' => [ 'state' => $search->state ] ];
                        }

                        $news_data = SearchController::news($search, $news_q, true);

                        if (count($news_data['data']))
                        {
                            $_histogram = $this->histogram('Haber', $news_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                            $_sentiment = $this->sentiment('Haber', $news_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                            $_category = $news_data['aggs']['category']['buckets']; if ($_category) $category[] = $_category;

                            if (count(@$news_data['aggs']['local_press']))
                            {
                                $local_press = $news_data['aggs']['local_press']['buckets'];
                            }

                            if (count($news_data['aggs']['hit_sites']['buckets']))
                            {
                                $_items = array_map(function($item) {
                                    return [
                                        'hit' => $item['doc_count'],
                                        'name' => @MediaCrawler::where('id', $item['key'])->value('name')
                                    ];
                                }, $news_data['aggs']['hit_sites']['buckets']);

                                $raw_datas[] = [
                                    'title' => 'Haber',
                                    'subtitle' => 'Konu hakkında en çok haber yazan haber siteleri.',
                                    'data' => $_items,
                                    'type' => 'data.newsSites'
                                ];
                            }

                            $stats['news']['unique_sites'] = @$news_data['aggs']['unique_sites']['value'];
                            $stats['news']['local_states'] = @$news_data['aggs']['local_states']['value'];

                            $stats['hits'] = $stats['hits'] + $news_data['aggs']['total']['value'];
                            $stats['counts']['media_article'] = $news_data['aggs']['total']['value'];

                            foreach ($news_data['data'] as $item)
                            {
                                $first_datas[$item['_type']][] = $item['_source'];
                            }
                        }
                    }
                break;
                case 'blog':
                    if ($organisation->data_blog)
                    {
                        $blog_q = $q;

                        unset($blog_q['aggs']['nws']);
                        unset($blog_q['aggs']['req']);
                        unset($blog_q['aggs']['que']);
                        unset($blog_q['aggs']['cmp']);

                        $blog_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                        $blog_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];
                        $blog_q['aggs']['category'] = [ 'terms' => [ 'field' => 'category', 'size' => 100 ] ];
                        $blog_q['aggs']['hit_sites'] = [
                            'terms' => [
                                'field' => 'site_id',
                                'size' => 100
                            ]
                        ];

                        $blog_data = SearchController::blog($search, $blog_q, true);

                        if (count($blog_data['data']))
                        {
                            $_histogram = $this->histogram('Blog', $blog_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                            $_sentiment = $this->sentiment('Blog', $blog_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                            $_category = $blog_data['aggs']['category']['buckets']; if ($_category) $category[] = $_category;

                            if (count($blog_data['aggs']['hit_sites']['buckets']))
                            {
                                $_items = array_map(function($item) {
                                    return [
                                        'hit' => $item['doc_count'],
                                        'name' => @BlogCrawler::where('id', $item['key'])->value('name')
                                    ];
                                }, $blog_data['aggs']['hit_sites']['buckets']);

                                $raw_datas[] = [
                                    'title' => 'Blog/Forum',
                                    'subtitle' => 'Konu hakkında en çok içerik girilen blog/forum siteleri.',
                                    'data' => $_items,
                                    'type' => 'data.blogSites'
                                ];
                            }

                            $stats['blog']['unique_sites'] = @$blog_data['aggs']['unique_sites']['value'];

                            $stats['hits'] = $stats['hits'] + $blog_data['aggs']['total']['value'];
                            $stats['counts']['blog_document'] = $blog_data['aggs']['total']['value'];
                        }
                    }
                break;
                case 'youtube_video':
                    if ($organisation->data_youtube_video)
                    {
                        $youtube_video_q = $q;

                        $youtube_video_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'channel.id' ] ];
                        $youtube_video_q['aggs']['hashtags'] = [ 'nested' => [ 'path' => 'tags' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'tags.tag' ] ] ] ];
                        $youtube_video_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];
                        $youtube_video_q['aggs']['gender'] = [ 'terms' => [ 'field' => 'channel.gender' ] ];
                        $youtube_video_q['aggs']['category'] = [ 'terms' => [ 'field' => 'category', 'size' => 100 ] ];
                        $youtube_video_q['aggs']['hashtag'] = [ 'nested' => [ 'path' => 'tags' ], 'aggs' => [ 'hits' => [ 'terms' => [ 'field' => 'tags.tag', 'size' => 25 ] ] ] ];
                        $youtube_video_q['aggs']['hit_users'] = [
                            'terms' => [
                                'field' => 'channel.id',
                                'size' => 100
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

                        $youtube_video_data = SearchController::youtube_video($search, $youtube_video_q, true);

                        if (count($youtube_video_data['data']))
                        {
                            $_histogram = $this->histogram('YouTube Video', $youtube_video_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                            $_sentiment = $this->sentiment('YouTube Video', $youtube_video_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                            $_consumer = $this->consumer('YouTube Video', $youtube_video_data['aggs']); if ($_consumer) $consumer[] = $_consumer;
                            $_gender = $youtube_video_data['aggs']['gender']; if ($_gender) $gender['youtube_video']['gender'] = $_gender;
                            $_category = $youtube_video_data['aggs']['category']['buckets']; if ($_category) $category[] = $_category;
                            $_hashtag = $youtube_video_data['aggs']['hashtag']['hits']['buckets']; if ($_hashtag) $hashtag['youtube'] = $_hashtag;

                            if (count($youtube_video_data['aggs']['hit_users']['buckets']))
                            {
                                $raw_datas[] = [
                                    'title' => 'YouTube Kullanıcıları',
                                    'subtitle' => 'Konu hakkında en çok video yükleyen YouTube kullanıcıları.',
                                    'data' => $youtube_video_data['aggs']['hit_users']['buckets'],
                                    'type' => 'data.youtubeUsers'
                                ];
                            }

                            $stats['youtube_video']['unique_users'] = @$youtube_video_data['aggs']['unique_users']['value'];
                            $stats['youtube_video']['hashtags'] = @$youtube_video_data['aggs']['hashtags']['doc_count'];

                            $stats['hits'] = $stats['hits'] + $youtube_video_data['aggs']['total']['value'];
                            $stats['counts']['youtube_video'] = $youtube_video_data['aggs']['total']['value'];
                        }
                    }
                break;
                case 'youtube_comment':
                    if ($organisation->data_youtube_comment)
                    {
                        $youtube_comment_q = $q;

                        $youtube_comment_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'channel.id' ] ];
                        $youtube_comment_q['aggs']['unique_videos'] = [ 'cardinality' => [ 'field' => 'video_id' ] ];
                        $youtube_comment_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];
                        $youtube_comment_q['aggs']['gender'] = [ 'terms' => [ 'field' => 'channel.gender' ] ];
                        $youtube_comment_q['aggs']['category'] = [ 'terms' => [ 'field' => 'category', 'size' => 100 ] ];
                        $youtube_comment_q['aggs']['hit_users'] = [
                            'terms' => [
                                'field' => 'channel.id',
                                'size' => 100
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

                        $youtube_comment_data = SearchController::youtube_comment($search, $youtube_comment_q, true);

                        if (count($youtube_comment_data['data']))
                        {
                            $_histogram = $this->histogram('YouTube Yorum', $youtube_comment_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                            $_sentiment = $this->sentiment('YouTube Yorum', $youtube_comment_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                            $_consumer = $this->consumer('YouTube Yorum', $youtube_comment_data['aggs']); if ($_consumer) $consumer[] = $_consumer;
                            $_gender = $youtube_comment_data['aggs']['gender']; if ($_gender) $gender['youtube_comment']['gender'] = $_gender;
                            $_category = $youtube_comment_data['aggs']['category']['buckets']; if ($_category) $category[] = $_category;

                            if (count($youtube_comment_data['aggs']['hit_users']['buckets']))
                            {
                                $raw_datas[] = [
                                    'title' => 'YouTube Kullanıcıları',
                                    'subtitle' => 'Konu hakkında en çok yorum yazan YouTube kullanıcıları.',
                                    'data' => $youtube_comment_data['aggs']['hit_users']['buckets'],
                                    'type' => 'data.youtubeComments'
                                ];
                            }

                            $stats['youtube_comment']['unique_users'] = @$youtube_comment_data['aggs']['unique_users']['value'];
                            $stats['youtube_comment']['unique_videos'] = @$youtube_comment_data['aggs']['unique_videos']['value'];

                            $stats['hits'] = $stats['hits'] + $youtube_comment_data['aggs']['total']['value'];
                            $stats['counts']['youtube_comment'] = $youtube_comment_data['aggs']['total']['value'];
                        }
                    }
                break;
                case 'shopping':
                    if ($organisation->data_shopping)
                    {
                        $shopping_q = $q;

                        unset($shopping_q['aggs']['nws']);
                        unset($shopping_q['aggs']['req']);
                        unset($shopping_q['aggs']['que']);
                        unset($shopping_q['aggs']['cmp']);

                        $shopping_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                        $shopping_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'seller.name' ] ];
                        $shopping_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];
                        $shopping_q['aggs']['gender'] = [ 'terms' => [ 'field' => 'seller.gender' ] ];
                        $shopping_q['aggs']['category'] = [ 'terms' => [ 'field' => 'category', 'size' => 100 ] ];
                        $shopping_q['aggs']['hit_users'] = [
                            'terms' => [
                                'field' => 'seller.name',
                                'size' => 100
                            ],
                            'aggs' => [
                                'properties' => [
                                    'top_hits' => [
                                        'size' => 1,
                                        '_source' => [
                                            'include' => [
                                                'site_id'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                        $shopping_q['aggs']['hit_sites'] = [
                            'terms' => [
                                'field' => 'site_id',
                                'size' => 100
                            ]
                        ];

                        $shopping_data = SearchController::shopping($search, $shopping_q, true);

                        if (count($shopping_data['data']))
                        {
                            $_histogram = $this->histogram('E-ticaret', $shopping_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                            $_sentiment = $this->sentiment('E-ticaret', $shopping_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                            $_gender = $shopping_data['aggs']['gender']; if ($_gender) $gender['shopping']['gender'] = $_gender;
                            $_category = $shopping_data['aggs']['category']['buckets']; if ($_category) $category[] = $_category;

                            if (count($shopping_data['aggs']['hit_sites']['buckets']))
                            {
                                $_items = array_map(function($item) {
                                    return [
                                        'hit' => $item['doc_count'],
                                        'name' => @ShoppingCrawler::where('id', $item['key'])->value('name')
                                    ];
                                }, $shopping_data['aggs']['hit_sites']['buckets']);

                                $raw_datas[] = [
                                    'title' => 'E-ticaret',
                                    'subtitle' => 'Konu hakkında ürün girilen e-ticaret siteleri.',
                                    'data' => $_items,
                                    'type' => 'data.shoppingSites'
                                ];
                            }

                            if (count($shopping_data['aggs']['hit_users']['buckets']))
                            {
                                $_items = array_map(function($item) {
                                    return [
                                        'hit' => $item['doc_count'],
                                        'site' => @ShoppingCrawler::where('id', $item['properties']['hits']['hits'][0]['_source']['site_id'])->value('name'),
                                        'name' => $item['key']
                                    ];
                                }, $shopping_data['aggs']['hit_users']['buckets']);

                                $raw_datas[] = [
                                    'title' => 'E-ticaret, Satıcılar',
                                    'subtitle' => 'Konu hakkında en çok ürün giren e-ticaret satıcıları.',
                                    'data' => $_items,
                                    'type' => 'data.shoppingUsers'
                                ];
                            }

                            $stats['shopping']['unique_sites'] = @$shopping_data['aggs']['unique_sites']['value'];
                            $stats['shopping']['unique_users'] = @$shopping_data['aggs']['unique_users']['value'];

                            $stats['hits'] = $stats['hits'] + $shopping_data['aggs']['total']['value'];
                            $stats['counts']['shopping_product'] = $shopping_data['aggs']['total']['value'];
                        }
                    }
                break;
            }
        }

        $mtime = explode(' ', microtime());
        $totaltime = $mtime[0] + $mtime[1] - $starttime;

        if ($stats['hits'])
        {
            $pages[] = [
                'title' => 'Sayılar',
                'subtitle' => 'Konuyla ilgili toplamda '.number_format($stats['hits']).' farklı içerik tespit edildi.',
                'sort' => $sort++,
                'data' => $stats,
                'type' => 'data.stats'
            ];

            if ($interval >= 120)
            {
                $pages[] = [
                    'title' => 'Yoğunluk',
                    'subtitle' => 'Saatlere göre yoğunluk grafiği.',
                    'sort' => $sort++,
                    'data' => [ 'chart' => [ 'height' => 400, 'type' => 'line', 'shadow' => [ 'enabled' => true, 'color' => '#000', 'top' => 18, 'left' => 7, 'blur' => 10, 'opacity' => 1 ], 'toolbar' => [ 'show' => false, 'tools' => [ 'download' => '<i class="material-icons">save</i>' ] ] ], 'dataLabels' => [ 'enabled' => true ], 'stroke' => [ 'width' => 2, 'curve' => 'smooth' ], 'series' => $histogram, 'title' => [ 'text' => 'Grafik', 'align' => 'left' ], 'grid' => [ 'borderColor' => 'transparent', 'row' => [ 'colors' => [ 'transparent', 'transparent' ] ] ], 'markers' => [ 'size' => 4 ], 'xaxis' => [ 'categories' => [ '00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00' ] ], 'legend' => [ 'position' => 'top', 'horizontalAlign' => 'right', 'floating' => true, 'offsetY' => -25, 'offsetX' => -5 ] ],
                    'type' => 'data.chart'
                ];
            }

            if (count($category))
            {
                $_categories = [];

                foreach ($category as $items)
                {
                    foreach ($items as $item)
                    {
                        $_categories[$item['key']] = @$_categories[$item['key']] ? $_categories[$item['key']] + $item['doc_count'] : $item['doc_count'];
                    }
                }

                asort($_categories);

                $pages[] = [
                    'title' => 'Kategoriler',
                    'subtitle' => 'Yayınlanan içeriklerin kategorilere dağılımı.',
                    'sort' => $sort++,
                    'data' => [ 'chart' => [ 'height' => 400, 'type' => 'bar', 'shadow' => [ 'enabled' => true, 'color' => '#000', 'top' => 18, 'left' => 7, 'blur' => 10, 'opacity' => 1 ], 'toolbar' => [ 'show' => false, 'tools' => [ 'download' => '<i class="material-icons">save</i>' ] ] ], 'dataLabels' => [ 'enabled' => true ], 'stroke' => [ 'width' => 0, 'curve' => 'smooth' ], 'series' => [ [ 'name' => 'İçerik', 'data' => array_values($_categories) ] ], 'title' => [ 'text' => 'Grafik', 'align' => 'left' ], 'grid' => [ 'borderColor' => '#e7e7e7', 'row' => [ 'colors' => ['#f3f3f3', 'transparent'], 'opacity' => 0.5 ] ], 'markers' => [ 'size' => 4 ], 'xaxis' => [ 'categories' => array_keys($_categories) ], 'legend' => [ 'position' => 'top', 'horizontalAlign' => 'right', 'floating' => true, 'offsetY' => -25, 'offsetX' => -5 ], 'plotOptions' => [ 'bar' => [ 'distributed' => true, 'horizontal' => true, 'barHeight' => '100%', 'dataLabels' => [ 'position' => 'bottom' ] ] ] ],
                    'type' => 'data.chart'
                ];
            }

            if (count($place))
            {
                foreach ($place as $key => $value)
                {
                    $pages[] = [
                        'title' => 'Lokasyon ('.$value['series']['name'].')',
                        'subtitle' => 'Konum bilgisine izin veren '.$value['series']['name'].' kullanıcılarından elde edilmiş başlıca lokasyonlar.',
                        'sort' => $sort++,
                        'data' => [ 'chart' => [ 'height' => 400, 'type' => 'bar', 'shadow' => [ 'enabled' => true, 'color' => '#000', 'top' => 18, 'left' => 7, 'blur' => 10, 'opacity' => 1 ], 'toolbar' => [ 'show' => false, 'tools' => [ 'download' => '<i class="material-icons">save<\/i>' ] ] ], 'dataLabels' => [ 'enabled' => true ], 'stroke' => [ 'width' => 0, 'curve' => 'smooth' ], 'series' => [ $value['series'] ], 'title' => [ 'text' => 'Grafik', 'align' => 'left' ], 'grid' => [ 'borderColor' => 'transparent', 'row' => [ 'colors' => [ 'transparent', 'transparent' ] ] ], 'markers' => [ 'size' => 4 ], 'xaxis' => [ 'categories' => $value['categories'] ], 'legend' => [ 'position' => 'top', 'horizontalAlign' => 'right', 'floating' => true, 'offsetY' => -25, 'offsetX' => -5 ], 'plotOptions' => [ 'bar' => [ 'distributed' => true, 'horizontal' => true, 'barHeight' => '100%', 'dataLabels' => [ 'position' => 'bottom' ] ] ] ],
                        'type' => 'data.chart'
                    ];
                }
            }

            if (count($platform))
            {
                foreach ($platform as $key => $value)
                {
                    $pages[] = [
                        'title' => 'Cihaz Bilgisi ('.$value['series']['name'].')',
                        'subtitle' => $value['series']['name'].' kullanıcılarının paylaşım yaptığı başlıca cihazlar.',
                        'sort' => $sort++,
                        'data' => [ 'chart' => [ 'height' => 400, 'type' => 'bar', 'shadow' => [ 'enabled' => true, 'color' => '#000', 'top' => 18, 'left' => 7, 'blur' => 10, 'opacity' => 1 ], 'toolbar' => [ 'show' => false, 'tools' => [ 'download' => '<i class="material-icons">save<\/i>' ] ] ], 'dataLabels' => [ 'enabled' => true ], 'stroke' => [ 'width' => 0, 'curve' => 'smooth' ], 'series' => [ $value['series'] ], 'title' => [ 'text' => 'Grafik', 'align' => 'left' ], 'grid' => [ 'borderColor' => 'transparent', 'row' => [ 'colors' => ['transparent', 'transparent'] ] ], 'markers' => [ 'size' => 4 ], 'xaxis' => [ 'categories' => $value['categories'] ], 'legend' => [ 'position' => 'top', 'horizontalAlign' => 'right', 'floating' => true, 'offsetY' => -25, 'offsetX' => -5 ], 'plotOptions' => [ 'bar' => [ 'distributed' => true, 'horizontal' => true, 'barHeight' => '100%', 'dataLabels' => [ 'position' => 'bottom' ] ] ] ],
                        'type' => 'data.chart'
                    ];
                }
            }

            if (count($sentiment))
            {
                $pages[] = [
                    'title' => 'Duygu Grafiği',
                    'subtitle' => 'Paylaşımların duygusal grafiği.',
                    'sort' => $sort++,
                    'data' => [ 'chart' => [ 'height' => 400, 'type' => 'line', 'shadow' => [ 'enabled' => true, 'color' => '#000', 'top' => 18, 'left' => 7, 'blur' => 10, 'opacity' => 1 ], 'toolbar' => [ 'show' => false, 'tools' => [ 'download' => '<i class="material-icons">save<\/i>' ] ] ], 'dataLabels' => [ 'enabled' => true ], 'stroke' => [ 'width' => 4, 'curve' => 'smooth' ], 'series' => $sentiment, 'title' => [ 'text' => 'Grafik', 'align' => 'left' ], 'grid' => [ 'borderColor' => 'transparent', 'row' => [ 'colors' => [ 'transparent', 'transparent' ] ] ], 'markers' => [ 'size' => 4 ], 'xaxis' => [ 'categories' => [ 'Pozitif', 'Nötr', 'Negatif', 'Nefret Söylemi' ] ], 'legend' => [ 'position' => 'top', 'horizontalAlign' => 'right', 'floating' => true, 'offsetY' => -25, 'offsetX' => -5 ] ],
                    'type' => 'data.chart'
                ];
            }

            if (count($consumer))
            {
                $pages[] = [
                    'title' => 'Soru, İstek, Şikayet ve Haber Grafiği',
                    'subtitle' => 'Paylaşımların niteliksel grafiği.',
                    'sort' => $sort++,
                    'data' => [ 'chart' => [ 'height' => 400, 'type' => 'line', 'shadow' => [ 'enabled' => true, 'color' => '#000', 'top' => 18, 'left' => 7, 'blur' => 10, 'opacity' => 1 ], 'toolbar' => [ 'show' => false, 'tools' => [ 'download' => '<i class="material-icons">save<\/i>' ] ] ], 'dataLabels' => [ 'enabled' => true ], 'stroke' => [ 'width' => 4, 'curve' => 'smooth' ], 'series' => $consumer, 'title' => [ 'text' => 'Grafik', 'align' => 'left' ], 'grid' => [ 'borderColor' => 'transparent', 'row' => [ 'colors' => [ 'transparent', 'transparent' ] ] ], 'markers' => [ 'size' => 4 ], 'xaxis' => [ 'categories' => [ 'İstek', 'Soru', 'Şikayet', 'Haber' ] ], 'legend' => [ 'position' => 'top', 'horizontalAlign' => 'right', 'floating' => true, 'offsetY' => -25, 'offsetX' => -5 ] ],
                    'type' => 'data.chart'
                ];
            }

            if (count($gender))
            {
                $pages[] = [
                    'title' => 'Cinsiyet Grafiği',
                    'subtitle' => 'Tespit edilen cinsiyet değerlerinin mecralara göre dağılımı.',
                    'sort' => $sort++,
                    'data' => $gender,
                    'type' => 'data.gender'
                ];
            }

            if (count($local_press))
            {
                $pages[] = [
                    'title' => 'Yerel Basın',
                    'subtitle' => 'Paylaşılan haberlerin yerel basın değerleri.',
                    'sort' => $sort++,
                    'data' => $local_press,
                    'type' => 'data.tr_map'
                ];
            }

            if (count($hashtag))
            {
                foreach ($hashtag as $key => $tag)
                {
                    $pages[] = [
                        'title' => 'Etiket Bulutu',
                        'subtitle' => title_case($key).' Sık kullanılan kelimelerden etiket bulutu.',
                        'sort' => $sort++,
                        'data' => $tag,
                        'type' => 'data.jcloud'
                    ];
                }
            }

            /* --------- */

            if ($this->report)
            {
                $report = $this->report;
            }
            else
            {
                $report = new Report;
                $report->name = implode(' ', [ $search->name, 'Son', intval($interval / 60), 'Saat' ]);
                $report->date_1 = date('Y-m-d');
                $report->organisation_id = $organisation->id;
                $report->user_id = config('app.user_id_support');
                $report->key = time().$organisation->author->id.$organisation->id.rand(1000, 1000000);
                $report->password = rand(1000, 9999);
                $report->save();
            }

            $insert = new ReportPage;
            $insert->report_id = $report->id;
            $insert->title = 'Otomatik Rapor';
            $insert->subtitle = 'Bu rapor Olive tarafından '.sprintf('%0.2f', $totaltime).' saniyede otomatik olarak oluşturulmuştur.';
            $insert->sort = 0;
            $insert->type = 'page.title';
            $insert->save();

            foreach ($pages as $page)
            {
                $insert = new ReportPage;
                $insert->report_id = $report->id;
                $insert->title = $page['title'];
                $insert->subtitle = $page['subtitle'];
                $insert->sort = $page['sort'];
                $insert->data = $page['data'];
                $insert->type = $page['type'];
                $insert->save();
            }

            if (count($raw_datas))
            {
                foreach ($raw_datas as $data)
                {
                    $insert = new ReportPage;
                    $insert->report_id = $report->id;
                    $insert->title = $data['title'];
                    $insert->subtitle = $data['subtitle'];
                    $insert->sort = $sort++;
                    $insert->data = $data['data'];
                    $insert->type = $data['type'];
                    $insert->save();
                }
            }

            if (count($first_datas))
            {
                foreach ($first_datas as $key => $datas)
                {
                    foreach ($datas as $i => $data)
                    {
                        $names = [
                            'entry' => 'Entry',
                            'article' => 'Haber'
                        ];

                        $insert = new ReportPage;
                        $insert->report_id = $report->id;
                        $insert->title = 'İlk '.$names[$key].' '.($i+1);
                        $insert->subtitle = $data['url'];
                        $insert->sort = $sort++;
                        $insert->data = $data;
                        $insert->type = implode('.', [ 'data', $key ]);
                        $insert->save();
                    }
                }
            }

            if (count($twitter_favorite_datas))
            {
                foreach ($twitter_favorite_datas as $i => $data)
                {
                    $insert = new ReportPage;
                    $insert->report_id = $report->id;
                    $insert->title = 'En Beğenilen Tweetler '.($i+1);
                    $insert->subtitle = 'https://twitter.com/'.$data['user']['screen_name'].'/status/'.$data['id'];
                    $insert->sort = $sort++;
                    $insert->data = $data;
                    $insert->type = 'data.tweet';
                    $insert->save();
                }
            }

            if (count($twitter_retweet_datas))
            {
                foreach ($twitter_retweet_datas as $i => $data)
                {
                    $insert = new ReportPage;
                    $insert->report_id = $report->id;
                    $insert->title = 'En Çok Etkileşim Alan Tweetler '.($i+1);
                    $insert->subtitle = 'https://twitter.com/'.$data['user']['screen_name'].'/status/'.$data['id'];
                    $insert->sort = $sort++;
                    $insert->data = $data;
                    $insert->type = 'data.tweet';
                    $insert->save();
                }
            }

            $insert = new ReportPage;
            $insert->report_id = $report->id;
            $insert->title = 'Daha fazlası için Olive\'i ziyaret edin!';
            $insert->subtitle = 'Olive ekranlarıyla gündemin olan bitenlerini eş zamanlı takip edebilirsiniz.';
            $insert->sort = $sort++;
            $insert->type = 'page.title';
            $insert->save();

            if ($this->report && $this->report->status)
            {
                $link = Link::generate(route('report.view', $report->key));

                $sms = SMS::send('Raporunuz hazır! '.$link, [ str_replace([ ' ', '(', ')' ], '', $report->gsm) ], false);
                $report->status = 'ok';
                $report->save();
            }

            if ($this->alarm)
            {
                $users = User::whereIn('id', $this->alarm->user_ids)->get();

                if (count($users))
                {
                    foreach ($users as $user)
                    {
                        $user->notify(
                            (
                                new AlarmReportNotification(
                                    $this->alarm,
                                    $user,
                                    $report
                                )
                            )->onQueue('email')
                        );
                    }
                }
            }
        }
        else
        {
            if ($this->report && $this->report->status)
            {
                $report = $this->report;

                if ($report->status)
                {
                    $sms = SMS::send('Girdiğiniz konu hakkında son 1 hafta içerisinde hiç sonuç bulunamadı.', [ str_replace([ ' ', '(', ')' ], '', $report->gsm) ]);
                }

                $report->status = 'failed';
                $report->save();
            }
        }
    }

    /*!
     * histogram pattern
     */
    private static function histogram(string $name, $data)
    {
        if (@$data['hourly']['buckets'])
        {
            return [
                'name' => $name,
                'data' => array_map(function($data) { return $data['doc_count']; }, $data['hourly']['buckets'])
            ];
        }
        else
        {
            return null;
        }
    }

    /*!
     * sentiment pattern
     */
    private static function sentiment(string $name, $data)
    {
        return [
            'name' => $name,
            'data' => [
                intval(@$data['pos']['doc_count']),
                intval(@$data['neu']['doc_count']),
                intval(@$data['neg']['doc_count']),
                intval(@$data['hte']['doc_count'])
            ]
        ];
    }

    /*!
     * consumer pattern
     */
    private static function consumer(string $name, $data)
    {
        return [
            'name' => $name,
            'data' => [
                intval(@$data['req']['doc_count']),
                intval(@$data['que']['doc_count']),
                intval(@$data['cmp']['doc_count']),
                intval(@$data['nws']['doc_count'])
            ]
        ];
    }

    /*!
     * place pattern
     */
    private static function place(string $name, $data)
    {
        if (@$data['place']['buckets'])
        {
            return [
                'series' => [
                    'name' => $name,
                    'data' => array_map(function($data) { return $data['doc_count']; }, $data['place']['buckets'])
                ],
                'categories' => array_map(function($data) { return $data['key']; }, $data['place']['buckets'])
            ];
        }
        else
        {
            return null;
        }
    }

    /*!
     * platform pattern
     */
    private static function platform(string $name, $data)
    {
        if (@$data['platform']['buckets'])
        {
            return [
                'series' => [
                    'name' => $name,
                    'data' => array_map(function($data) { return $data['doc_count']; }, $data['platform']['buckets'])
                ],
                'categories' => array_map(function($data) { return $data['key']; }, $data['platform']['buckets'])
            ];
        }
        else
        {
            return null;
        }
    }

    /*!
     * orjinal tweet
     */
    private static function originalTweet($item)
    {
        $result = null;

        if (@$item['_source']['external']['id'])
        {
            $original = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
                'query' => [
                    'bool' => [ 'must' => [ 'match' => [ 'id' => $item['_source']['external']['id'] ] ] ]
                ]
            ]);

            if ($original->status == 'ok' && @$original->data['hits']['hits'][0])
            {
                $result = $original->data['hits']['hits'][0]['_source'];
            }
        }

        return $result;
    }
}
