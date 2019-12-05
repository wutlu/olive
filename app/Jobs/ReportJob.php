<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\SavedSearch;
use App\Models\Report;
use App\Models\ReportPage;

use App\Http\Controllers\SearchController;

use Term;

use App\Elasticsearch\Document;

class ReportJob// implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $search;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SavedSearch $search)
    {
        $this->search = $search;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $search = $this->search;
        $organisation = $search->organisation;

        $sort = 2;

        $report = new Report;
        $report->name = implode(' ', [ $search->name, $search->report == 'daily' ? 'Günlük' : 'Saatlik', 'Rapor' ]);
        $report->date_1 = date('Y-m-d');
        $report->organisation_id = $organisation->id;
        $report->user_id = config('app.user_id_support');
        $report->key = time().$organisation->author->id.$organisation->id.rand(1000, 1000000);
        $report->save();

        $clean = Term::cleanSearchQuery($search->string);

        $q = [
            'from' => 0,
            'size' => 1,
            'sort' => [ 'created_at' => 'asc' ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd HH',
                                    'gte' => date('Y-m-d', strtotime('-30 days')).' 08'
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

        foreach ($search->modules as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
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

                        $tweet_data = SearchController::tweet($search, $twitter_q, true);

                        $_histogram = $this->histogram('Twitter', $tweet_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                        $_sentiment = $this->sentiment('Twitter', $tweet_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                        $_consumer = $this->consumer('Twitter', $tweet_data['aggs']); if ($_consumer) $consumer[] = $_consumer;
                        $_gender = $tweet_data['aggs']['gender']; if ($_gender) $gender['twitter']['gender'] = $_gender;
                        $_place = $this->place('Twitter', $tweet_data['aggs']); if ($_place) $place['twitter'] = $_place;
                        $_platform = $this->platform('Twitter', $tweet_data['aggs']); if ($_platform) $platform['twitter'] = $_platform;

                        $stats['twitter']['mentions'] = @$tweet_data['aggs']['mentions']['doc_count'];
                        $stats['twitter']['hashtags'] = @$tweet_data['aggs']['hashtags']['doc_count'];
                        $stats['twitter']['unique_users'] = @$tweet_data['aggs']['unique_users']['value'];
                        $stats['twitter']['verified_users'] = @$tweet_data['aggs']['verified_users']['doc_count'];
                        $stats['twitter']['followers'] = @$tweet_data['aggs']['followers']['value'];
                        $stats['twitter']['reach'] = @$tweet_data['aggs']['reach']['sum_other_doc_count'];

                        $stats['hits'] = $stats['hits'] + $tweet_data['aggs']['total']['value'];
                        $stats['counts']['twitter_tweet'] = $tweet_data['aggs']['total']['value'];

                        if (count($tweet_data['data']))
                        {
                            foreach ($tweet_data['data'] as $item)
                            {
                                $first_datas[] = $item;
                            }
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

                        $instagram_data = SearchController::instagram($search, $instagram_q, true);

                        $_histogram = $this->histogram('Instagram', $instagram_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                        $_sentiment = $this->sentiment('Instagram', $instagram_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                        $_consumer = $this->consumer('Instagram', $instagram_data['aggs']); if ($_consumer) $consumer[] = $_consumer;

                        $stats['instagram']['mentions'] = @$instagram_data['aggs']['mentions']['doc_count'];
                        $stats['instagram']['hashtags'] = @$instagram_data['aggs']['hashtags']['doc_count'];
                        $stats['instagram']['unique_users'] = @$instagram_data['aggs']['unique_users']['value'];

                        $stats['hits'] = $stats['hits'] + $instagram_data['aggs']['total']['value'];
                        $stats['counts']['instagram_media'] = $instagram_data['aggs']['total']['value'];

                        if (count($instagram_data['data']))
                        {
                            foreach ($instagram_data['data'] as $item)
                            {
                                $first_datas[] = $item;
                            }
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

                        $sozluk_data = SearchController::sozluk($search, $sozluk_q, true);

                        $_histogram = $this->histogram('Sözlük', $sozluk_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                        $_sentiment = $this->sentiment('Sözlük', $sozluk_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                        $_consumer = $this->consumer('Sözlük', $sozluk_data['aggs']); if ($_consumer) $consumer[] = $_consumer;
                        $_gender = $sozluk_data['aggs']['gender']; if ($_gender) $gender['sozluk']['gender'] = $_gender;

                        $stats['sozluk']['unique_users'] = @$sozluk_data['aggs']['unique_users']['value'];
                        $stats['sozluk']['unique_topics'] = @$sozluk_data['aggs']['unique_topics']['value'];
                        $stats['sozluk']['unique_sites'] = @$sozluk_data['aggs']['unique_sites']['value'];

                        $stats['hits'] = $stats['hits'] + $sozluk_data['aggs']['total']['value'];
                        $stats['counts']['sozluk_entry'] = $sozluk_data['aggs']['total']['value'];

                        if (count($sozluk_data['data']))
                        {
                            foreach ($sozluk_data['data'] as $item)
                            {
                                $first_datas[] = $item;
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

                        if ($search->state)
                        {
                            $news_q['query']['bool']['must'][] = [ 'match' => [ 'state' => $search->state ] ];
                        }

                        $news_data = SearchController::news($search, $news_q, true);

                        $_histogram = $this->histogram('Haber', $news_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                        $_sentiment = $this->sentiment('Haber', $news_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;

                        if (count(@$news_data['aggs']['local_press']))
                        {
                            $local_press = $news_data['aggs']['local_press']['buckets'];
                        }

                        $stats['news']['unique_sites'] = @$news_data['aggs']['unique_sites']['value'];
                        $stats['news']['local_states'] = @$news_data['aggs']['local_states']['value'];

                        $stats['hits'] = $stats['hits'] + $news_data['aggs']['total']['value'];
                        $stats['counts']['media_article'] = $news_data['aggs']['total']['value'];

                        if (count($news_data['data']))
                        {
                            foreach ($news_data['data'] as $item)
                            {
                                $first_datas[] = $item;
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

                        $blog_data = SearchController::blog($search, $blog_q, true);

                        $_histogram = $this->histogram('Blog', $blog_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                        $_sentiment = $this->sentiment('Blog', $blog_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;

                        $stats['blog']['unique_sites'] = @$blog_data['aggs']['unique_sites']['value'];

                        $stats['hits'] = $stats['hits'] + $blog_data['aggs']['total']['value'];
                        $stats['counts']['blog_document'] = $blog_data['aggs']['total']['value'];

                        if (count($blog_data['data']))
                        {
                            foreach ($blog_data['data'] as $item)
                            {
                                $first_datas[] = $item;
                            }
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

                        $youtube_video_data = SearchController::youtube_video($search, $youtube_video_q, true);

                        $_histogram = $this->histogram('YouTube Video', $youtube_video_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                        $_sentiment = $this->sentiment('YouTube Video', $youtube_video_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                        $_consumer = $this->consumer('YouTube Video', $youtube_video_data['aggs']); if ($_consumer) $consumer[] = $_consumer;
                        $_gender = $youtube_video_data['aggs']['gender']; if ($_gender) $gender['youtube_video']['gender'] = $_gender;

                        $stats['youtube_video']['unique_users'] = @$youtube_video_data['aggs']['unique_users']['value'];
                        $stats['youtube_video']['hashtags'] = @$youtube_video_data['aggs']['hashtags']['doc_count'];

                        $stats['hits'] = $stats['hits'] + $youtube_video_data['aggs']['total']['value'];
                        $stats['counts']['youtube_video'] = $youtube_video_data['aggs']['total']['value'];

                        if (count($youtube_video_data['data']))
                        {
                            foreach ($youtube_video_data['data'] as $item)
                            {
                                $first_datas[] = $item;
                            }
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

                        $youtube_comment_data = SearchController::youtube_comment($search, $youtube_comment_q, true);

                        $_histogram = $this->histogram('YouTube Yorum', $youtube_comment_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                        $_sentiment = $this->sentiment('YouTube Yorum', $youtube_comment_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                        $_consumer = $this->consumer('YouTube Yorum', $youtube_comment_data['aggs']); if ($_consumer) $consumer[] = $_consumer;
                        $_gender = $youtube_comment_data['aggs']['gender']; if ($_gender) $gender['youtube_comment']['gender'] = $_gender;

                        $stats['youtube_comment']['unique_users'] = @$youtube_comment_data['aggs']['unique_users']['value'];
                        $stats['youtube_comment']['unique_videos'] = @$youtube_comment_data['aggs']['unique_videos']['value'];

                        $stats['hits'] = $stats['hits'] + $youtube_comment_data['aggs']['total']['value'];
                        $stats['counts']['youtube_comment'] = $youtube_comment_data['aggs']['total']['value'];

                        if (count($youtube_comment_data['data']))
                        {
                            foreach ($youtube_comment_data['data'] as $item)
                            {
                                $first_datas[] = $item;
                            }
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

                        $shopping_data = SearchController::shopping($search, $shopping_q, true);

                        $_histogram = $this->histogram('E-ticaret', $shopping_data['aggs']); if ($_histogram) $histogram[] = $_histogram;
                        $_sentiment = $this->sentiment('E-ticaret', $shopping_data['aggs']); if ($_sentiment) $sentiment[] = $_sentiment;
                        $_gender = $shopping_data['aggs']['gender']; if ($_gender) $gender['shopping']['gender'] = $_gender;

                        $stats['shopping']['unique_sites'] = @$shopping_data['aggs']['unique_sites']['value'];
                        $stats['shopping']['unique_users'] = @$shopping_data['aggs']['unique_users']['value'];

                        $stats['hits'] = $stats['hits'] + $shopping_data['aggs']['total']['value'];
                        $stats['counts']['shopping_product'] = $shopping_data['aggs']['total']['value'];

                        if (count($shopping_data['data']))
                        {
                            foreach ($shopping_data['data'] as $item)
                            {
                                $first_datas[] = $item;
                            }
                        }
                    }
                break;
            }
        }

        $page = new ReportPage;
        $page->report_id = $report->id;
        $page->title = 'Sayılar';
        $page->subtitle = 'Konuyla ilgili toplamda '.number_format($stats['hits']).' farklı içerik tespit edildi.';
        $page->sort = 1;
        $page->data = $stats;
        $page->type = 'data.stats';
        $page->save();

        if ($search->report == 'daily')
        {
            $page = new ReportPage;
            $page->report_id = $report->id;
            $page->title = 'Yoğunluk';
            $page->subtitle = 'Saatlere göre yoğunluk grafiği.';
            $page->sort = $sort;
            $page->data = [ 'chart' => [ 'height' => 400, 'type' => 'line', 'shadow' => [ 'enabled' => true, 'color' => '#000', 'top' => 18, 'left' => 7, 'blur' => 10, 'opacity' => 1 ], 'toolbar' => [ 'show' => false, 'tools' => [ 'download' => '<i class=\'material-icons\'>save</i>' ] ] ], 'dataLabels' => [ 'enabled' => true ], 'stroke' => [ 'width' => 2, 'curve' => 'smooth' ], 'series' => $histogram, 'title' => [ 'text' => 'Grafik', 'align' => 'left' ], 'grid' => [ 'borderColor' => 'transparent', 'row' => [ 'colors' => [ 'transparent', 'transparent' ] ] ], 'markers' => [ 'size' => 4 ], 'xaxis' => [ 'categories' => [ '00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00' ] ], 'legend' => [ 'position' => 'top', 'horizontalAlign' => 'right', 'floating' => true, 'offsetY' => -25, 'offsetX' => -5 ]];
            $page->type = 'data.chart';
            $page->save();

            $sort++;
        }


        if (count($place))
        {
            foreach ($place as $key => $value)
            {
                $page = new ReportPage;
                $page->report_id = $report->id;
                $page->title = 'Lokasyon ('.$value['series']['name'].')';
                $page->subtitle = 'Konum bilgisi aktif olan '.$value['series']['name'].' kullanıcılarından elde edilmiş başlıca lokasyonlar.';
                $page->sort = $sort;
                $page->data = [ 'chart' => [ 'height' => 400, 'type' => 'bar', 'shadow' => [ 'enabled' => true, 'color' => '#000', 'top' => 18, 'left' => 7, 'blur' => 10, 'opacity' => 1 ], 'toolbar' => [ 'show' => false, 'tools' => [ 'download' => '<i class=\'material-icons\'>save<\/i>' ] ] ], 'dataLabels' => [ 'enabled' => true ], 'stroke' => [ 'width' => 0, 'curve' => 'smooth' ], 'series' => [ $value['series'] ], 'title' => [ 'text' => 'Grafik', 'align' => 'left' ], 'grid' => [ 'borderColor' => 'transparent', 'row' => [ 'colors' => [ 'transparent', 'transparent' ] ] ], 'markers' => [ 'size' => 4 ], 'xaxis' => [ 'categories' => $value['categories'] ], 'legend' => [ 'position' => 'top', 'horizontalAlign' => 'right', 'floating' => true, 'offsetY' => -25, 'offsetX' => -5 ], 'plotOptions' => [ 'bar' => [ 'distributed' => true, 'horizontal' => true, 'barHeight' => '100%', 'dataLabels' => [ 'position' => 'bottom' ] ] ] ];
                $page->type = 'data.chart';
                $page->save();

                $sort++;
            }
        }


        if (count($platform))
        {
            foreach ($platform as $key => $value)
            {
                $page = new ReportPage;
                $page->report_id = $report->id;
                $page->title = 'Cihaz Bilgisi ('.$value['series']['name'].')';
                $page->subtitle = $value['series']['name'].' kullanıcılarının paylaşım yaptığı başlıca cihazlar.';
                $page->sort = $sort;
                $page->data = [ 'chart' => [ 'height' => 400, 'type' => 'bar', 'shadow' => [ 'enabled' => true, 'color' => '#000', 'top' => 18, 'left' => 7, 'blur' => 10, 'opacity' => 1 ], 'toolbar' => [ 'show' => false, 'tools' => [ 'download' => '<i class=\'material-icons\'>save<\/i>' ] ] ], 'dataLabels' => [ 'enabled' => true ], 'stroke' => [ 'width' => 0, 'curve' => 'smooth' ], 'series' => [ $value['series'] ], 'title' => [ 'text' => 'Grafik', 'align' => 'left' ], 'grid' => [ 'borderColor' => 'transparent', 'row' => [ 'colors' => ['transparent', 'transparent'] ] ], 'markers' => [ 'size' => 4 ], 'xaxis' => [ 'categories' => $value['categories'] ], 'legend' => [ 'position' => 'top', 'horizontalAlign' => 'right', 'floating' => true, 'offsetY' => -25, 'offsetX' => -5 ], 'plotOptions' => [ 'bar' => [ 'distributed' => true, 'horizontal' => true, 'barHeight' => '100%', 'dataLabels' => [ 'position' => 'bottom' ] ] ] ];
                $page->type = 'data.chart';
                $page->save();

                $sort++;
            }
        }

        $page = new ReportPage;
        $page->report_id = $report->id;
        $page->title = 'Duygu Grafiği';
        $page->sort = $sort;
        $page->data = [ 'chart' => [ 'height' => 400, 'type' => 'line', 'shadow' => [ 'enabled' => true, 'color' => '#000', 'top' => 18, 'left' => 7, 'blur' => 10, 'opacity' => 1 ], 'toolbar' => [ 'show' => false, 'tools' => [ 'download' => '<i class=\'material-icons\'>save<\/i>' ] ] ], 'dataLabels' => [ 'enabled' => true ], 'stroke' => [ 'width' => 4, 'curve' => 'smooth' ], 'series' => $sentiment, 'title' => [ 'text' => 'Grafik', 'align' => 'left' ], 'grid' => [ 'borderColor' => 'transparent', 'row' => [ 'colors' => [ 'transparent', 'transparent' ] ] ], 'markers' => [ 'size' => 4 ], 'xaxis' => [ 'categories' => [ 'Pozitif', 'Nötr', 'Negatif', 'Nefret Söylemi' ] ], 'legend' => [ 'position' => 'top', 'horizontalAlign' => 'right', 'floating' => true, 'offsetY' => -25, 'offsetX' => -5 ] ];
        $page->type = 'data.chart';
        $page->save();

        $sort++;

        $page = new ReportPage;
        $page->report_id = $report->id;
        $page->title = 'Soru, İstek, Şikayet ve Haber Grafiği';
        $page->sort = $sort;
        $page->data = [ 'chart' => [ 'height' => 400, 'type' => 'line', 'shadow' => [ 'enabled' => true, 'color' => '#000', 'top' => 18, 'left' => 7, 'blur' => 10, 'opacity' => 1 ], 'toolbar' => [ 'show' => false, 'tools' => [ 'download' => '<i class=\'material-icons\'>save<\/i>' ] ] ], 'dataLabels' => [ 'enabled' => true ], 'stroke' => [ 'width' => 4, 'curve' => 'smooth' ], 'series' => $consumer, 'title' => [ 'text' => 'Grafik', 'align' => 'left' ], 'grid' => [ 'borderColor' => 'transparent', 'row' => [ 'colors' => [ 'transparent', 'transparent' ] ] ], 'markers' => [ 'size' => 4 ], 'xaxis' => [ 'categories' => [ 'İstek', 'Soru', 'Şikayet', 'Haber' ] ], 'legend' => [ 'position' => 'top', 'horizontalAlign' => 'right', 'floating' => true, 'offsetY' => -25, 'offsetX' => -5 ] ];
        $page->type = 'data.chart';
        $page->save();

        $sort++;

        if (count($gender))
        {
            $page = new ReportPage;
            $page->report_id = $report->id;
            $page->title = 'Cinsiyet Grafiği';
            $page->subtitle = 'İçerik yayınlayan kullanıcıların cinsiyet dağılımı.';
            $page->sort = $sort;
            $page->data = $gender;
            $page->type = 'data.gender';
            $page->save();
        }

        $sort++;

        if (count($local_press))
        {
            $page = new ReportPage;
            $page->report_id = $report->id;
            $page->title = 'Yerel Basın';
            $page->subtitle = 'Konu ile ilgili haber paylaşan yerel basın siteleri.';
            $page->sort = $sort;
            $page->data = $local_press;
            $page->type = 'data.tr_map';
            $page->save();

            $sort++;
        }
    }

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
}
