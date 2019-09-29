<?php

namespace App\Console\Commands\Alarm;

use Illuminate\Console\Command;

use App\Models\Alarm;
use App\Models\User\User;

use DB;
use Mail;
use Term;

use App\Mail\AlarmMail;

use App\Elasticsearch\Document;
use App\Models\Source;

use Carbon\Carbon;

use App\Http\Controllers\SearchController;

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
            $this->info('['.count($alarms).'] alarm!');

            foreach ($alarms as $alarm)
            {
                $es_data = self::elasticsearch($alarm);

                $this->info($alarm->search->name.' - ['.count($es_data['data']).']');

                if (count($es_data['data']))
                {
                    $this->info(count($es_data['data']));

                    $stats[] = '| '.implode(' | ', [
                        number_format($es_data['stats']['mentions']),
                        number_format($es_data['stats']['hashtags']),
                        number_format($es_data['stats']['unique_users']),
                        number_format($es_data['stats']['reach'])
                    ]).' |';
                    $stats[] = '|---:|---:|---:|---:|---:|';
                    $stats[] = '| '.implode(' | ', [ '_Mention_', '_Hashtag_', '_Tekil_', '_Tahmini Ulaşılan_' ]).' |';

                    $sources[] = '| '.implode(' | ', [ '_Kaynak_', '_İçerik_' ]).' |';
                    $sources[] = '|:---|---:|';

                    foreach (json_decode($alarm->search->modules) as $module)
                    {
                        $sources[] = '| '.config('system.modules')[$module].' | '.number_format(intval(@$es_data['data'][$module]['count'])).' |';
                    }

                    foreach ($es_data['data'] as $item)
                    {
                        $data[] = implode(
                            ' ',
                            [
                                PHP_EOL,
                                PHP_EOL,
                                str_replace([ '#' ], [ ' \#'], $item['text']),
                                route('content',
                                    [
                                        'es_index' => $item['_index'],
                                        'es_type' => $item['_type'],
                                        'es_id' => $item['_id']
                                    ]
                                )
                            ]
                        );
                    }

                    $alarm->update([
                        'sended_at' => date('Y-m-d H:i:s'),
                        'hit' => $alarm->hit-1
                    ]);

                    Mail::queue(
                        new AlarmMail(
                            [
                                'stats' => implode(PHP_EOL, $stats),
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
        $organisation = $alarm->organisation;

        $search = $alarm->search;

        preg_match_all('/(?<=\[s:)[([0-9]+(?=\])/m', $search->string, $matches);

        if (@$matches[0][0])
        {
            $source = Source::whereIn('id', $matches[0])->where('organisation_id', $organisation->id)->first();
            $search->string = preg_replace('/\[s:([0-9]+)\]/m', '', $search->string);
        }

        $clean = Term::cleanSearchQuery($search->string);
        $searchController = new SearchController;

        $q = [
            'size' => 1,
            'sort' => [ 'created_at' => 'desc' ],
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

        foreach ([ [ 'consumer' => [ 'nws', 'que', 'req', 'cmp' ] ], [ 'sentiment' => [ 'pos', 'neg', 'neu', 'hte' ] ] ] as $key => $bucket)
        {
            foreach ($bucket as $key => $b)
            {
                foreach ($b as $o)
                {
                    if ($search->{$key.'_'.$o})
                    {
                        $q['query']['bool']['filter'][] = [
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
        $stats = [
            'mentions' => 0,
            'hashtags' => 0,
            'unique_users' => 0,
            'reach' => 0
        ];

        foreach (json_decode($search->modules) as $module)
        {
            switch ($module)
            {
                case 'twitter':
                    if ($organisation->data_twitter)
                    {
                        $twitter_q = $q;

                        $twitter_q['aggs']['mentions'] = [
                            'nested' => [ 'path' => 'entities.mentions' ],
                            'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.mentions.mention.id' ] ] ]
                        ];
                        $twitter_q['aggs']['hashtags'] = [
                            'nested' => [ 'path' => 'entities.hashtags' ],
                            'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.hashtags.hashtag' ] ] ]
                        ];
                        $twitter_q['aggs']['unique_users'] = [
                            'cardinality' => [ 'field' => 'user.id' ]
                        ];
                        $twitter_q['aggs']['reach'] = [
                            'avg' => [ 'field' => 'user.counts.followers' ]
                        ];

                        $item = $searchController->tweet($search, $twitter_q);

                        $stats['mentions'] = $stats['mentions'] + $item['aggs']['mentions']['doc_count'];
                        $stats['hashtags'] = $stats['hashtags'] + $item['aggs']['hashtags']['doc_count'];
                        $stats['unique_users'] = $stats['unique_users'] + $item['aggs']['unique_users']['value'];
                        $stats['reach'] = $stats['reach'] + intval($item['aggs']['reach']['value']);

                        if (@$item['data'][0])
                        {
                            $data['twitter'] = [
                                '_id' => $item['data'][0]['_id'],
                                '_type' => $item['data'][0]['_type'],
                                '_index' => $item['data'][0]['_index'],

                                'text' => $item['data'][0]['text'],
                                'count' => $item['stats']['total']
                            ];
                        }
                    }
                break;
                case 'instagram':
                    if ($organisation->data_instagram)
                    {
                        $instagram_q = $q;

                        $instagram_q['aggs']['mentions'] = [
                            'nested' => [ 'path' => 'entities.mentions' ],
                            'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.mentions.mention.id' ] ] ]
                        ];
                        $instagram_q['aggs']['hashtags'] = [
                            'nested' => [ 'path' => 'entities.hashtags' ],
                            'aggs' => [ 'xxx' => [ 'terms' => [ 'field' => 'entities.hashtags.hashtag' ] ] ]
                        ];
                        $instagram_q['aggs']['unique_users'] = [
                            'cardinality' => [ 'field' => 'user.id' ]
                        ];

                        $item = $searchController->instagram($search, $q);

                        if (@$item['data'][0])
                        {
                            $data['instagram'] = [
                                '_id' => $item['data'][0]['_id'],
                                '_type' => $item['data'][0]['_type'],
                                '_index' => $item['data'][0]['_index'],

                                'text' => $item['data'][0]['text'],
                                'count' => $item['stats']['total']
                            ];
                        }
                    }
                break;
                case 'sozluk':
                    if ($organisation->data_sozluk)
                    {
                        $item = $searchController->sozluk($search, $q, @$source->source_sozluk);

                        if (@$item['data'][0])
                        {
                            $data['sozluk'] = [
                                '_id' => $item['data'][0]['_id'],
                                '_type' => $item['data'][0]['_type'],
                                '_index' => $item['data'][0]['_index'],

                                'text' => $item['data'][0]['title'],
                                'count' => $item['stats']['total']
                            ];
                        }
                    }
                break;
                case 'news':
                    if ($organisation->data_news)
                    {
                        $item = $searchController->news($search, $q, @$source->source_media);

                        if (@$item['data'][0])
                        {
                            $data['news'] = [
                                '_id' => $item['data'][0]['_id'],
                                '_type' => $item['data'][0]['_type'],
                                '_index' => $item['data'][0]['_index'],

                                'text' => $item['data'][0]['title'],
                                'count' => $item['stats']['total']
                            ];
                        }
                    }
                break;
                case 'blog':
                    if ($organisation->data_blog)
                    {
                        $item = $searchController->blog($search, $q, @$source->source_blog);

                        if (@$item['data'][0])
                        {
                            $data['blog'] = [
                                '_id' => $item['data'][0]['_id'],
                                '_type' => $item['data'][0]['_type'],
                                '_index' => $item['data'][0]['_index'],

                                'text' => $item['data'][0]['title'],
                                'count' => $item['stats']['total']
                            ];
                        }
                    }
                break;
                case 'youtube_video':
                    if ($organisation->data_youtube_video)
                    {
                        $item = $searchController->youtube_video($search, $q);

                        if (@$item['data'][0])
                        {
                            $data['youtube_video'] = [
                                '_id' => $item['data'][0]['_id'],
                                '_type' => $item['data'][0]['_type'],
                                '_index' => $item['data'][0]['_index'],

                                'text' => $item['data'][0]['title'],
                                'count' => $item['stats']['total']
                            ];
                        }
                    }
                break;
                case 'youtube_comment':
                    if ($organisation->data_youtube_comment)
                    {
                        $item = $searchController->youtube_comment($search, $q);

                        if (@$item['data'][0])
                        {
                            $data['youtube_comment'] = [
                                '_id' => $item['data'][0]['_id'],
                                '_type' => $item['data'][0]['_type'],
                                '_index' => $item['data'][0]['_index'],

                                'text' => $item['data'][0]['text'],
                                'count' => $item['stats']['total']
                            ];
                        }
                    }
                break;
                case 'shopping':
                    if ($organisation->data_shopping)
                    {
                        $item = $searchController->shopping($search, $q, @$source->source_shopping);

                        if (@$item['data'][0])
                        {
                            $data['shopping'] = [
                                '_id' => $item['data'][0]['_id'],
                                '_type' => $item['data'][0]['_type'],
                                '_index' => $item['data'][0]['_index'],

                                'text' => $item['data'][0]['title'],
                                'count' => $item['stats']['total']
                            ];
                        }
                    }
                break;
            }
        }

        return [
            'data' => $data,
            'stats' => $stats
        ];
    }
}
