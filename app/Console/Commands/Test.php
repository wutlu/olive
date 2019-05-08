<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Twitter\StreamingUsers;

use App\Elasticsearch\Document;

use System;

use Term;

use App\Olive\Gender;
use App\Olive\Sentiment as OliveSentiment;

use Carbon\Carbon;

use Sentiment;

use App\Models\Analysis;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test komutu.';

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
        if (System::option('data.learn') == 'on')
        {
            $array = config('system.analysis');

            unset($array['gender']);

            foreach ($array as $key => $analysis)
            {
                $this->info('----------['.$key.']----------');

                foreach ($analysis['types'] as $k => $a)
                {
                    $this->line($k);

                    $query = Document::search([ 'sozluk', '*' ], 'entry', [
                        'query' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'range' => [
                                            'created_at' => [
                                                'format' => 'YYYY-MM-dd HH:mm',
                                                'gte' => Carbon::now()->subDays(1)->format('Y-m-d H:i')
                                            ]
                                        ],
                                    ],
                                    [
                                        'range' => [ str_replace('-', '.', $k) => [ 'gte' => 0.9 ] ],
                                    ]
                                ]
                            ]
                        ],
                        'aggs' => [
                            'top_hits' => [
                                'terms' => [
                                    'field' => 'entry',
                                    'size' => 100,
                                    'script' => '(
                                        (_value.length() >= 3 && _value.length() <= 20) &&
                                        (
                                            _value != \'t.co\' &&
                                            _value != \'http\' &&
                                            _value != \'https\'
                                        )
                                    ) ? _value : \'_null_\''
                                ]
                            ]
                        ],
                        'size' => 0
                    ]);

                    if ($query->status == 'ok')
                    {
                        $this->info('[ok]');

                        if (count($query->data['aggregations']['top_hits']['buckets']))
                        {
                            $data = [];

                            foreach ($query->data['aggregations']['top_hits']['buckets'] as $row)
                            {
                                if ($row['key'] != '_null_')
                                {
                                    $slug = OliveSentiment::_getTokens($row['key']);

                                    $exists = Analysis::where('module', $key)->where('word', 'ILIKE', '%'.$slug[0].'%')->exists();

                                    if (!$exists)
                                    {
                                        $data[] = $slug[0];
                                    }
                                }
                            }

                            print_r($data);
                        }
                        else
                        {
                            $this->error('word not found.');
                        }
                    }
                }
            }
        }
        else
        {
            $this->error('Makine öğrenmesi aktif değil.');
        }
    }
}
