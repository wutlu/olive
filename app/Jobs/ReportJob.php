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

        $first_datas = [];

        foreach ($search->modules as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
                        $twitter_q = $q;

                            $twitter_q['size'] = 10;
                            $twitter_q['sort'] = [ 'counts.retweet' => 'desc' ];
                            $twitter_q['aggs']['mentions'] = [ 'nested' => [ 'path' => 'entities.mentions' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.mentions.mention.id' ] ] ] ];
                            $twitter_q['aggs']['hashtags'] = [ 'nested' => [ 'path' => 'entities.hashtags' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.hashtags.hashtag' ] ] ] ];
                            $twitter_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'user.id' ] ];
                            $twitter_q['aggs']['verified_users'] = [ 'filter' => [ 'exists' => [ 'field' => 'user.verified' ] ] ];
                            $twitter_q['aggs']['followers'] = [ 'avg' => [ 'field' => 'user.counts.followers' ] ];
                            $twitter_q['aggs']['reach'] = [ 'terms' => [ 'field' => 'external.id' ] ];
                            $twitter_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];

                        $tweet_data = SearchController::tweet($search, $twitter_q, true);

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

                        $instagram_data = SearchController::instagram($search, $instagram_q, true);

                            $stats['instagram']['mentions'] = @$instagram_data['aggs']['mentions']['doc_count'];
                            $stats['instagram']['hashtags'] = @$instagram_data['aggs']['hashtags']['doc_count'];
                            $stats['instagram']['unique_users'] = @$instagram_data['aggs']['unique_users']['value'];

                        $stats['hits'] = $stats['hits'] + $instagram_data['aggs']['total']['value'];
                        $stats['counts']['instagram_media'] = $instagram_data['aggs']['total']['value'];

                        if (@$instagram_data['data'][0])
                        {
                            $first_datas[] = $instagram_data['data'][0];
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

                        $sozluk_data = SearchController::sozluk($search, $sozluk_q, true);

                            $stats['sozluk']['unique_users'] = @$sozluk_data['aggs']['unique_users']['value'];
                            $stats['sozluk']['unique_topics'] = @$sozluk_data['aggs']['unique_topics']['value'];
                            $stats['sozluk']['unique_sites'] = @$sozluk_data['aggs']['unique_sites']['value'];

                        $stats['hits'] = $stats['hits'] + $sozluk_data['aggs']['total']['value'];
                        $stats['counts']['sozluk_entry'] = $sozluk_data['aggs']['total']['value'];

                        if (@$sozluk_data['data'][0])
                        {
                            $first_datas[] = $sozluk_data['data'][0];
                        }
                    }
                break;
                case 'news':
                    if ($organisation->data_news)
                    {
                        $news_q = $q;

                            $news_q['size'] = 10;
                            $news_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                            $news_q['aggs']['local_states'] = [ 'cardinality' => [ 'field' => 'state' ] ];
                            $news_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];

                        if ($search->state)
                        {
                            $news_q['query']['bool']['must'][] = [ 'match' => [ 'state' => $search->state ] ];
                        }

                        $news_data = SearchController::news($search, $news_q, true);

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

                            $blog_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                            $blog_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];

                        $blog_data = SearchController::blog($search, $blog_q, true);

                            $stats['blog']['unique_sites'] = @$blog_data['aggs']['unique_sites']['value'];

                        $stats['hits'] = $stats['hits'] + $blog_data['aggs']['total']['value'];
                        $stats['counts']['blog_document'] = $blog_data['aggs']['total']['value'];

                        if (@$blog_data['data'][0])
                        {
                            $first_datas[] = $blog_data['data'][0];
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

                        $youtube_video_data = SearchController::youtube_video($search, $youtube_video_q, true);

                            $stats['youtube_video']['unique_users'] = @$youtube_video_data['aggs']['unique_users']['value'];
                            $stats['youtube_video']['hashtags'] = @$youtube_video_data['aggs']['hashtags']['doc_count'];

                        $stats['hits'] = $stats['hits'] + $youtube_video_data['aggs']['total']['value'];
                        $stats['counts']['youtube_video'] = $youtube_video_data['aggs']['total']['value'];

                        if (@$youtube_video_data['data'][0])
                        {
                            $first_datas[] = $youtube_video_data['data'][0];
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

                        $youtube_comment_data = SearchController::youtube_comment($search, $youtube_comment_q, true);

                            $stats['youtube_comment']['unique_users'] = @$youtube_comment_data['aggs']['unique_users']['value'];
                            $stats['youtube_comment']['unique_videos'] = @$youtube_comment_data['aggs']['unique_videos']['value'];

                        $stats['hits'] = $stats['hits'] + $youtube_comment_data['aggs']['total']['value'];
                        $stats['counts']['youtube_comment'] = $youtube_comment_data['aggs']['total']['value'];

                        if (@$youtube_comment_data['data'][0])
                        {
                            $first_datas[] = $youtube_comment_data['data'][0];
                        }
                    }
                break;
                case 'shopping':
                    if ($organisation->data_shopping)
                    {
                        $shopping_q = $q;

                            $shopping_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                            $shopping_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'seller.name' ] ];
                            $shopping_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];

                        $shopping_data = SearchController::shopping($search, $shopping_q, true);

                            $stats['shopping']['unique_sites'] = @$shopping_data['aggs']['unique_sites']['value'];
                            $stats['shopping']['unique_users'] = @$shopping_data['aggs']['unique_users']['value'];

                        $stats['hits'] = $stats['hits'] + $shopping_data['aggs']['total']['value'];
                        $stats['counts']['shopping_product'] = $shopping_data['aggs']['total']['value'];

                        if (@$shopping_data['data'][0])
                        {
                            $first_datas[] = $shopping_data['data'][0];
                        }
                    }
                break;
            }
        }








































        $report = new Report;
        $report->name = implode(' ', [ $search->name, $search->report == 'daily' ? 'Günlük' : 'Saatlik', 'Rapor' ]);
        $report->date_1 = date('Y-m-d');
        $report->organisation_id = $organisation->id;
        $report->user_id = config('app.user_id_support');
        $report->key = time().$organisation->author->id.$organisation->id.rand(1000, 1000000);
        $report->save();

        $page = new ReportPage;
        $page->report_id = $report->id;
        $page->title = 'Sayılar';
        $page->subtitle = 'Konunun mecralara göre yayılma değerleri.';
        $page->sort = 1;
        $page->data = $stats;
        $page->type = 'data.stats';
        $page->save();

        $sort = 1;
        $i_tweet = 1;
        $i_news = 1;

        foreach ($first_datas as $data)
        {
            $sort++;

            $page = new ReportPage;
            $page->type = implode('.', [ 'data', $data['_type'] ]);

            switch ($data['_type'])
            {
                case 'tweet':
                    $page->title = 'Twitter, En Çok Etkileşim Alan '.$i_tweet.'/10';
                    $page->subtitle = 'https://twitter.com/'.$data['user']['screen_name'].'/status/'.$data['_id'];

                    $i_tweet++;

                    $text = [ 'Bu Tweet son '.([ 'daily' => 24, 'hourly' => 1 ][$search->report]).' saat içerisindeki değerlere göre rapora eklenmiştir.' ];

                    if (@$data['_source']['deleted_at'])
                    {
                        $text[] = 'İçerik '.$data['_source']['deleted_at'].' tarihinde silinmiş.';
                    }

                    $page->text = implode(PHP_EOL.PHP_EOL, $text);

                    if (@$data['_source']['external']['id'])
                    {
                        $original = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
                            'query' => [
                                'bool' => [ 'must' => [ 'match' => [ 'id' => $data['_source']['external']['id'] ] ] ]
                            ]
                        ]);

                        if ($original->status == 'ok' && @$original->data['hits']['hits'][0])
                        {
                            $data['_source']['original'] = $original->data['hits']['hits'][0]['_source'];
                        }
                    }
                break;
                case 'article':
                    $page->title = 'Başlıca Haberler '.$i_news.'/10';
                    $page->subtitle = $data['url'];

                    $i_news++;

                    $page->text = 'Bu haber son '.([ 'daily' => 24, 'hourly' => 1 ][$search->report]).' saat içerisindeki değerlere göre rapora eklenmiştir.';
                break;
                case 'entry':
                    $page->title = 'Sözlük, İlk Paylaşım';
                    $page->subtitle = $data['url'];
                    $page->text = 'Bir önceki gün sabah 08:00 itibariyle, ilgili konu hakkında girilen ilk entry.';
                break;
                case 'media':
                    $page->title = 'Instagram, İlk Paylaşım';
                    $page->subtitle = $data['url'];
                    $page->text = 'Bir önceki gün sabah 08:00 itibariyle, ilgili konu hakkında paylaşılan ilk medya.';

                    if (@$data['_source']['user']['id'])
                    {
                        $external = Document::get([ 'instagram', 'users' ], 'user', $data['_source']['user']['id']);

                        if ($external->status == 'ok')
                        {
                            $data['_source']['user'] = $external->data['_source'];
                        }
                    }
                break;
                case 'document':
                    $page->title = 'Blog, İlk Paylaşım';
                    $page->subtitle = $data['url'];
                    $page->text = 'Bir önceki gün sabah 08:00 itibariyle, ilgili konu hakkında yazılan ilk yazı.';
                break;
                case 'comment':
                    $page->title = 'YouTube, Yorum, İlk Paylaşım';
                    $page->subtitle = 'https://www.youtube.com/watch?v='.$data['video_id'];
                    $page->text = 'Bir önceki gün sabah 08:00 itibariyle, ilgili konu hakkında yapılan ilk YouTube yorumu.';

                    $video = Document::get([ 'youtube', 'videos' ], 'video', $data['_source']['video_id']);

                    if ($video->status == 'ok')
                    {
                        $data['_source']['video'] = $video->data['_source'];
                    }
                break;
                case 'video':
                    $page->title = 'YouTube, Video, İlk Paylaşım';
                    $page->subtitle = 'https://www.youtube.com/watch?v='.$data['_id'];
                    $page->text = 'Bir önceki gün sabah 08:00 itibariyle, ilgili konu hakkında paylaşılan ilk YouTube videosu.';
                break;
                case 'product':
                    $page->title = 'E-ticaret, İlk Paylaşım';
                    $page->subtitle = $data['url'];
                    $page->text = 'Bir önceki gün sabah 08:00 itibariyle, ilgili konu hakkında paylaşılan ilk e-ticaret içeriği.';
                break;
            }
            $page->report_id = $report->id;
            $page->sort = $sort;
            $page->data = $data['_source'];
            $page->save();
        }
    }
}
