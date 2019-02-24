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

                $this->info($alarm->name);

                if (count($es_data))
                {
                    $this->info(count($es_data));

                    $sources[] = '| Kaynak  | İçerik |';
                    $sources[] = '|:--------|-------:|';

                    foreach ($alarm->modules as $module)
                    {
                        $sources[] = '| '.config('system.modules')[$module].' | '.intval(@$es_data[$module]['count']).' |';
                    }

                    foreach ($es_data as $item)
                    {
                        $data[] = '['.str_replace('#', '', $item['text']).']('.route('content', [
                            'es_index' => $item['_index'],
                            'es_type' => $item['_type'],
                            'es_id' => $item['_id']
                        ]).')';
                    }

                    $alarm->update([
                        'sended_at' => date('Y-m-d H:i:s'),
                        'hit' => $alarm->hit-1
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
                }
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

                $query = @Document::list([ 'twitter', 'tweets', date('Y.m') ], 'tweet', $q);

                if (@$query->data['hits']['hits'])
                {
                    foreach ($query->data['hits']['hits'] as $object)
                    {
                        $data['twitter'] = [
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],

                            'text' => $object['_source']['text'],
                            'count' => $query->data['hits']['total']
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

                $query = Document::list([ 'media', '*' ], 'article', $q);

                if (@$query->data['hits']['hits'])
                {
                    foreach ($query->data['hits']['hits'] as $object)
                    {
                        $data['news'] = [
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],

                            'text' => $object['_source']['title'],
                            'count' => $query->data['hits']['total']
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

                $query = Document::list([ 'sozluk', '*' ], 'entry', $q);

                if (@$query->data['hits']['hits'])
                {
                    foreach ($query->data['hits']['hits'] as $object)
                    {
                        $data['sozluk'] = [
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],

                            'text' => $object['_source']['title'],
                            'count' => $query->data['hits']['total']
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

                $query = @Document::list([ 'shopping', '*' ], 'product', $q);

                if (@$query->data['hits']['hits'])
                {
                    foreach ($query->data['hits']['hits'] as $object)
                    {
                        $data['shopping'] = [
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],

                            'text' => $object['_source']['title'],
                            'count' => $query->data['hits']['total']
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

                $query = @Document::list([ 'youtube', 'videos' ], 'video', $q);

                if (@$query->data['hits']['hits'])
                {
                    foreach ($query->data['hits']['hits'] as $object)
                    {
                        $data['youtube_video'] = [
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],

                            'text' => $object['_source']['title'],
                            'count' => $query->data['hits']['total']
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

                $query = @Document::list([ 'youtube', 'comments', '*' ], 'comment', $q);

                if (@$query->data['hits']['hits'])
                {
                    foreach ($query->data['hits']['hits'] as $object)
                    {
                        $data['youtube_comment'] = [
                            '_id' => $object['_id'],
                            '_type' => $object['_type'],
                            '_index' => $object['_index'],

                            'text' => $object['_source']['text'],
                            'count' => $query->data['hits']['total']
                        ];
                    }
                }
            }
        }

        return $data;
    }
}
