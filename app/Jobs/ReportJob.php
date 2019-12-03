<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\SavedSearch;

use App\Http\Controllers\SearchController;

use Term;

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
            'size' => 10,
            'sort' => [ 'created_at' => $search->reverse ? 'asc' : 'desc' ],
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

        $data = [];

        foreach ($search->modules as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
                        $twitter_q = $q;

                        if ($search->twitter_sort)
                        {
                            $twitter_q['sort'] = [ $search->twitter_sort => $search->twitter_sort_operator ];
                        }

                        $tweet_data = SearchController::tweet($search, $twitter_q);

                        $data = array_merge($data, $tweet_data['data']);
                    }
                break;
                case 'instagram':
                    if ($organisation->data_instagram)
                    {
                        $instagram_q = $q;

                        $instagram_data = SearchController::instagram($search, $instagram_q);

                        $data = array_merge($data, $instagram_data['data']);
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

                        $sozluk_data = SearchController::sozluk($search, $sozluk_q);

                            $stats['sozluk']['unique_users'] = @$sozluk_data['aggs']['unique_users']['value'];
                            $stats['sozluk']['unique_topics'] = @$sozluk_data['aggs']['unique_topics']['value'];
                            $stats['sozluk']['unique_sites'] = @$sozluk_data['aggs']['unique_sites']['value'];

                        $stats['hits'] = $stats['hits'] + $sozluk_data['aggs']['total']['value'];
                        $stats['counts']['sozluk_entry'] = $sozluk_data['aggs']['total']['value'];

                        $data = array_merge($data, $sozluk_data['data']);
                    }
                break;
                case 'news':
                    if ($organisation->data_news)
                    {
                        $news_q = $q;

                            $news_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                            $news_q['aggs']['local_states'] = [ 'cardinality' => [ 'field' => 'state' ] ];
                            $news_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];

                        if ($search->state)
                        {
                            $news_q['query']['bool']['must'][] = [ 'match' => [ 'state' => $search->state ] ];
                        }

                        $news_data = SearchController::news($search, $news_q);

                            $stats['news']['unique_sites'] = @$news_data['aggs']['unique_sites']['value'];
                            $stats['news']['local_states'] = @$news_data['aggs']['local_states']['value'];

                        $stats['hits'] = $stats['hits'] + $news_data['aggs']['total']['value'];
                        $stats['counts']['media_article'] = $news_data['aggs']['total']['value'];

                        $data = array_merge($data, $news_data['data']);
                    }
                break;
                case 'blog':
                    if ($organisation->data_blog)
                    {
                        $blog_q = $q;

                            $blog_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                            $blog_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];

                        $blog_data = SearchController::blog($search, $blog_q);

                            $stats['blog']['unique_sites'] = @$blog_data['aggs']['unique_sites']['value'];

                        $stats['hits'] = $stats['hits'] + $blog_data['aggs']['total']['value'];
                        $stats['counts']['blog_document'] = $blog_data['aggs']['total']['value'];

                        $data = array_merge($data, $blog_data['data']);
                    }
                break;
                case 'youtube_video':
                    if ($organisation->data_youtube_video)
                    {
                        $youtube_video_q = $q;

                            $youtube_video_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'channel.id' ] ];
                            $youtube_video_q['aggs']['hashtags'] = [ 'nested' => [ 'path' => 'tags' ], 'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'tags.tag' ] ] ] ];
                            $youtube_video_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];

                        $youtube_video_data = SearchController::youtube_video($search, $youtube_video_q);

                            $stats['youtube_video']['unique_users'] = @$youtube_video_data['aggs']['unique_users']['value'];
                            $stats['youtube_video']['hashtags'] = @$youtube_video_data['aggs']['hashtags']['doc_count'];

                        $stats['hits'] = $stats['hits'] + $youtube_video_data['aggs']['total']['value'];
                        $stats['counts']['youtube_video'] = $youtube_video_data['aggs']['total']['value'];

                        $data = array_merge($data, $youtube_video_data['data']);
                    }
                break;
                case 'youtube_comment':
                    if ($organisation->data_youtube_comment)
                    {
                        $youtube_comment_q = $q;

                            $youtube_comment_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'channel.id' ] ];
                            $youtube_comment_q['aggs']['unique_videos'] = [ 'cardinality' => [ 'field' => 'video_id' ] ];
                            $youtube_comment_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];

                        $youtube_comment_data = SearchController::youtube_comment($search, $youtube_comment_q);

                            $stats['youtube_comment']['unique_users'] = @$youtube_comment_data['aggs']['unique_users']['value'];
                            $stats['youtube_comment']['unique_videos'] = @$youtube_comment_data['aggs']['unique_videos']['value'];

                        $stats['hits'] = $stats['hits'] + $youtube_comment_data['aggs']['total']['value'];
                        $stats['counts']['youtube_comment'] = $youtube_comment_data['aggs']['total']['value'];

                        $data = array_merge($data, $youtube_comment_data['data']);
                    }
                break;
                case 'shopping':
                    if ($organisation->data_shopping)
                    {
                        $shopping_q = $q;

                            $shopping_q['aggs']['unique_sites'] = [ 'cardinality' => [ 'field' => 'site_id' ] ];
                            $shopping_q['aggs']['unique_users'] = [ 'cardinality' => [ 'field' => 'seller.name' ] ];
                            $shopping_q['aggs']['total'] = [ 'value_count' => [ 'field' => 'id' ] ];

                        $shopping_data = SearchController::shopping($search, $shopping_q);

                            $stats['shopping']['unique_sites'] = @$shopping_data['aggs']['unique_sites']['value'];
                            $stats['shopping']['unique_users'] = @$shopping_data['aggs']['unique_users']['value'];

                        $stats['hits'] = $stats['hits'] + $shopping_data['aggs']['total']['value'];
                        $stats['counts']['shopping_product'] = $shopping_data['aggs']['total']['value'];

                        $data = array_merge($data, $shopping_data['data']);
                    }
                break;
            }
        }

        if ($search->twitter_sort)
        {
            $data = array_reverse($data);
        }
        else
        {
            usort($data, '\App\Utilities\DateUtility::dateSort');
        }

        if (!$search->reverse)
        {
            $data = array_reverse($data);
        }

        print_r($data);
    }
}
