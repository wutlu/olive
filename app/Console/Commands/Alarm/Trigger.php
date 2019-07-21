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

        $alarms = Alarm::where('hit', '>', 0)
                       ->where('weekdays', 'LIKE', '%'.$day.'%')
                       ->where('start_time', '<=', date('H:i'))
                       ->where('end_time', '>=', date('H:i'))
                       ->where('sended_at', '<=', DB::raw("NOW() - INTERVAL '1 minutes' * interval"))
                       ->get();

        if (count($alarms))
        {
            $this->info('...');

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
                        $data[] = implode(' ', [ $item['text'], route('content', [
                            'es_index' => $item['_index'],
                            'es_type' => $item['_type'],
                            'es_id' => $item['_id']
                        ]) ]);
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
        else
        {
            $this->error('Bu periyotta bir alarm bulunamadı.');
        }
    }

    private static function elasticsearch(Alarm $alarm)
    {
        $mquery = [
            'size' => 1,
            'sort' => [
                'called_at' => 'DESC'
            ],
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'called_at' => [
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
            switch ($module)
            {
                case 'twitter':
                    $q = $mquery;

                    $q['query']['bool']['must'][] = [
                        'query_string' => [
                            'default_field' => 'text',
                            'query' => $alarm->query,
                            'default_operator' => 'AND'
                        ]
                    ];
                    $q['query']['bool']['must_not'][] = [ 'match' => [ 'external.type' => 'retweet' ] ];
                    $q['_source'] = [
                        'user.name',
                        'user.screen_name',
                        'text'
                    ];

                    $query = Document::search([ 'twitter', 'tweets', date('Y.m') ], 'tweet', $q);

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
                break;
                case 'news':
                    $q = $mquery;

                    $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];
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
                    $q['_source'] = [
                        'title'
                    ];

                    $query = Document::search([ 'media', '*' ], 'article', $q);

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
                break;
                case 'blog':
                    $q = $mquery;

                    $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];
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
                    $q['_source'] = [
                        'title'
                    ];

                    $query = Document::search([ 'blog', '*' ], 'document', $q);

                    if (@$query->data['hits']['hits'])
                    {
                        foreach ($query->data['hits']['hits'] as $object)
                        {
                            $data['blog'] = [
                                '_id' => $object['_id'],
                                '_type' => $object['_type'],
                                '_index' => $object['_index'],

                                'text' => $object['_source']['title'],
                                'count' => $query->data['hits']['total']
                            ];
                        }
                    }
                break;
                case 'sozluk':
                    $q = $mquery;

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
                    $q['_source'] = [
                        'title'
                    ];

                    $query = Document::search([ 'sozluk', '*' ], 'entry', $q);

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
                break;
                case 'shopping':
                    $q = $mquery;

                    $q['query']['bool']['must'][] = [ 'match' => [ 'status' => 'ok' ] ];
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
                    $q['_source'] = [
                        'title'
                    ];

                    $query = Document::search([ 'shopping', '*' ], 'product', $q);

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
                break;
                case 'youtube_video':
                    $q = $mquery;

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
                    $q['_source'] = [
                        'title',
                        'channel.title'
                    ];

                    $query = Document::search([ 'youtube', 'videos' ], 'video', $q);

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
                break;
                case 'youtube_comment':
                    $q = $mquery;

                    $q['query']['bool']['must'][] = [
                        'query_string' => [
                            'default_field' => 'text',
                            'query' => $alarm->query,
                            'default_operator' => 'AND'
                        ]
                    ];
                    $q['_source'] = [
                        'text',
                        'channel.title'
                    ];

                    $query = Document::search([ 'youtube', 'comments', '*' ], 'comment', $q);

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
                break;
                case 'instagram':
                    $q = $mquery;

                    $q['query']['bool']['must'][] = [
                        'query_string' => [
                            'default_field' => 'text',
                            'query' => $alarm->query,
                            'default_operator' => 'AND'
                        ]
                    ];
                    $q['_source'] = [
                        'text'
                    ];

                    $query = Document::search([ 'instagram', 'medias', '*' ], 'media', $q);

                    if (@$query->data['hits']['hits'])
                    {
                        foreach ($query->data['hits']['hits'] as $object)
                        {
                            $data['instagram'] = [
                                '_id' => $object['_id'],
                                '_type' => $object['_type'],
                                '_index' => $object['_index'],

                                'text' => $object['_source']['text'],
                                'count' => $query->data['hits']['total']
                            ];
                        }
                    }
                break;
            }
        }

        return $data;
    }
}
