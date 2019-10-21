<?php

namespace App\Console\Commands\Trend;

use Illuminate\Console\Command;

use App\Models\PopTrend;

use App\Elasticsearch\Document;

class PopCategorization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trend:pop_categorization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Popüler kaynakları kategorize eder.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $query = PopTrend::where('month_key', date('Ym'))->where('trend_hit', '>=', 10)->get();

        if (count($query))
        {
            $this->info('Kategorize Edilecek: '.count($query));

            $i = 0;

            foreach ($query as $item)
            {
                $q = [
                    'size' => 0,
                    'query' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'range' => [ 'created_at' => [ 'format' => 'YYYY-MM-dd', 'gte' => date('Y-m-d', strtotime('-1 months')) ] ]
                                ],
                                [
                                    'exists' => [ 'field' => 'category' ]
                                ]
                            ]
                        ]
                    ],
                    'aggs' => [
                        'category' => [
                            'terms' => [ 'field' => 'category', 'size' => 1 ]
                        ]
                    ]
                ];

                switch ($item->module)
                {
                    case 'twitter_tweet':
                        $index = [ 'twitter', 'tweets', '*' ];
                        $type = 'tweet';
                        $q['query']['bool']['must'][] = [
                            'match' => [
                                'user.id' => $item->details['id']
                            ]
                        ];
                    break;
                    case 'twitter_favorite':
                        $index = [ 'twitter', 'tweets', '*' ];
                        $type = 'tweet';
                        $q['query']['bool']['must'][] = [
                            'match' => [
                                'user.id' => $item->details['id']
                            ]
                        ];
                    break;
                    case 'twitter_hashtag':
                        $index = [ 'twitter', 'tweets', '*' ];
                        $type = 'tweet';
                        $q['query']['bool']['must'][] = [
                            'query_string' => [
                                'fields' => [
                                    'text'
                                ],
                                'query' => '"'.$item->details['title'].'"',
                                'default_operator' => 'AND'
                            ]
                        ];
                    break;
                    case 'instagram_hashtag':
                        $index = [ 'instagram', 'medias', '*' ];
                        $type = 'media';
                        $q['query']['bool']['must'][] = [
                            'query_string' => [
                                'fields' => [
                                    'text'
                                ],
                                'query' => '"'.$item->details['title'].'"',
                                'default_operator' => 'AND'
                            ]
                        ];
                    break;
                    case 'entry':
                        $index = [ 'sozluk', '*' ];
                        $type = 'entry';
                        $q['query']['bool']['must'][] = [
                            'match' => [
                                'title' => $item->details['title']
                            ]
                        ];
                    break;
                    case 'youtube_video':
                        $index = [ 'youtube', 'videos' ];
                        $type = 'video';
                        $q['query']['bool']['must'][] = [
                            'match' => [
                                'channel.id' => $item->details['id']
                            ]
                        ];
                    break;
                    case 'google':
                        $index = [ 'twitter', 'tweets', '*' ];
                        $type = 'tweet';
                        $q['query']['bool']['must'][] = [
                            'query_string' => [
                                'fields' => [
                                    'text'
                                ],
                                'query' => $item->details['title'],
                                'default_operator' => 'AND'
                            ]
                        ];
                    break;
                }

                $es = Document::search($index, $type, $q);

                if ($es->status == 'ok')
                {
                    if (@$es->data['aggregations']['category']['buckets'][0])
                    {
                        $item->update([ 'category' => $es->data['aggregations']['category']['buckets'][0]['key'] ]);
                        $i++;
                    }
                }
            }

            $this->info('Kategorize Edildi: '.$i);
        }
    }
}
