<?php

namespace App\Console\Commands\Alarm;

use Illuminate\Console\Command;

use App\Models\Alarm;
use App\Models\User\User;

use DB;
use Mail;

use App\Mail\AlarmMail;

use App\Elasticsearch\Document;

class Trigger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alarm:trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zamanı gelmiş alarmların kontrolü.';

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
        $day = implode('_', [ 'day', intval(date('N')) ]);

        $alarms = Alarm::where('hit', '<>', 0)
                       ->where('weekdays', 'LIKE', '%'.$day.'%')
                       ->where('start_time', '<=', date('H:i'))
                       ->where('end_time', '>=', date('H:i'))
                       ->where('sended_at', '<=', DB::raw("NOW() - INTERVAL '1 minutes' * interval"))
                       ->get();

        if (count($alarms))
        {
            foreach ($alarms as $alarm)
            {
                $es_data = self::elasticsearch($alarm);

                $sources[] = '| Kaynak  | İçerik |';
                $sources[] = '|:--------|-------:|';

                foreach ($alarm->modules as $module)
                {
                    $sources[] = '| '.config('system.modules')[$module].' | 1 |';
                }

                $data[] = '[Örnek İçerik](https://google.com)';
                $data[] = '[Örnek İçerik](https://google.com)';
                $data[] = '[Örnek İçerik](https://google.com)';
                $data[] = '[Örnek İçerik](https://google.com)';
                $data[] = '[Örnek İçerik](https://google.com)';
                $data[] = '[Örnek İçerik](https://google.com)';

                $alarm->update([
                    //'sended_at' => date('Y-m-d H:i:s'),
                    //'hit' => $alarm->hit-1
                ]);

                Mail::queue(
                    new AlarmMail(
                        [
                            'data' => $data,
                            'alarm' => $alarm,
                            'sources' => implode(PHP_EOL, $sources)
                        ]
                    )
                );

                $this->info($alarm->name);
            }
        }
    }

    private static function elasticsearch(Alarm $alarm)
    {
        $mquery = [
            'size' => 1,
            'sort' => [ 'created_at' => 'DESC' ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd HH:mm',
                                    'gte' => date('Y-m-d H:i', strtotime('-'.$alarm->interval.' minutes'))
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $data = [];

        foreach ($alarm->modules as $module)
        {
            ### [ twitter modülü ] ###
            if ($module == 'twitter')
            {
                $q = $mquery;

                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'default_field' => 'text',
                        'query' => $alarm->query,
                        'default_operator' => 'AND'
                    ]
                ];
                $q['query']['bool']['must_not'][] = [ 'match' => [ 'external.type' => 'retweet' ] ];
                $q['_source'] = [ 'user.name', 'user.screen_name', 'text' ];

                $query = @Document::list([ 'twitter', 'tweets', date('Y.m') ], 'tweet', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            '_id' => $object['_id'], '_type' => $object['_type'], '_index' => $object['_index'],
                            'module' => 'twitter',
                            'user' => [
                                'name' => $object['_source']['user']['name'],
                                'screen_name' => $object['_source']['user']['screen_name']
                            ],
                            'text' => $object['_source']['text']
                        ];
                    }
                }
            }

            ### [ haber modülü ] ###
            if ($module == 'news')
            {
                $q = $mquery;

                $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];
                $q['_source'] = [ 'title' ];
                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'fields' => [
                            'description',
                            'title'
                        ],
                        'query' => $alarm->query,
                        'default_operator' => 'AND'
                    ]
                ];

                $query = @Document::list([ 'media', '*' ], 'article', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            '_id' => $object['_id'], '_type' => $object['_type'], '_index' => $object['_index'],
                            'module' => 'haber',
                            'title' => $object['_source']['title']
                        ];
                    }
                }
            }

            ### [ sözlük modülü ] ###
            if ($module == 'sozluk')
            {
                $q = $mquery;

                $q['_source'] = [ 'title' ];
                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'fields' => [
                            'description',
                            'title'
                        ],
                        'query' => $alarm->query,
                        'default_operator' => 'AND'
                    ]
                ];

                $query = @Document::list([ 'sozluk', '*' ], 'entry', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            '_id' => $object['_id'], '_type' => $object['_type'], '_index' => $object['_index'],
                            'module' => 'sozluk',
                            'title' => $object['_source']['title']
                        ];
                    }
                }
            }

            ### [ alışveriş modülü ] ###
            if ($module == 'shopping')
            {
                $q = $mquery;

                $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];
                $q['_source'] = [ 'title' ];
                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'fields' => [
                            'description',
                            'title'
                        ],
                        'query' => $alarm->query,
                        'default_operator' => 'AND'
                    ]
                ];

                $query = @Document::list([ 'shopping', '*' ], 'product', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            '_id' => $object['_id'], '_type' => $object['_type'], '_index' => $object['_index'],
                            'module' => 'alisveris',
                            'title' => $object['_source']['title']
                        ];
                    }
                }
            }

            ### [ youtube, video modülü ] ###
            if ($module == 'youtube_video')
            {
                $q = $mquery;

                $q['_source'] = [ 'title', 'channel.title' ];
                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'fields' => [
                            'description',
                            'title'
                        ],
                        'query' => $alarm->query,
                        'default_operator' => 'AND'
                    ]
                ];

                $query = @Document::list([ 'youtube', 'videos' ], 'video', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            '_id' => $object['_id'], '_type' => $object['_type'], '_index' => $object['_index'],
                            'module' => 'youtube-video',
                            'channel' => [
                                'title' => $object['_source']['channel']['title']
                            ],
                            'title' => $object['_source']['title']
                        ];
                    }
                }
            }

            ### [ youtube, yorum modülü ] ###
            if ($module == 'youtube_comment')
            {
                $q = $mquery;

                $q['_source'] = [ 'text', 'channel.title' ];
                $q['query']['bool']['must'][] = [
                    'query_string' => [
                        'default_field' => 'text',
                        'query' => $alarm->query,
                        'default_operator' => 'AND'
                    ]
                ];

                $query = @Document::list([ 'youtube', 'comments', '*' ], 'comment', $q)->data['hits']['hits'];

                if ($query)
                {
                    foreach ($query as $object)
                    {
                        $data[] = [
                            '_id' => $object['_id'], '_type' => $object['_type'], '_index' => $object['_index'],
                            'module' => 'youtube-comment',
                            'channel' => [
                                'title' => $object['_source']['channel']['title']
                            ],
                            'text' => $object['_source']['text']
                        ];
                    }
                }
            }
        }

        print_r($data);

        exit();
    }
}
