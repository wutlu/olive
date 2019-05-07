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
use Sense;

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
        $gender = new Gender;
        $gender->loadNames();

        print_r($gender->detector([ 'ahmet' ]));

        exit();
        $query = Document::search([ 'twitter', 'tweets', '*' ], 'tweet', [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'created_at' => [
                                    'format' => 'YYYY-MM-dd',
                                    'gte' => Carbon::now()->subDays(1)->format('Y-m-d')
                                ]
                            ],
                        ],
                        [
                            'range' => [ 'sentiment.pos' => [ 'gte' => 0.9 ] ],
                        ]
                    ],
                    'must_not' => [
                        [ 'exists' => [ 'field' => 'external.id' ] ],
                    ]
                ]
            ],
            'aggs' => [
                'top_hits' => [
                    'terms' => [
                        'field' => 'text',
                        'size' => 20,
                        'script' => '(
                            (_value.length() > 3 && _value.length() <= 20) &&
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

        if ($query->status == 'ok' && count($query->data['aggregations']['top_hits']['buckets']))
        {
            $data = [];
            
            foreach ($query->data['aggregations']['top_hits']['buckets'] as $row)
            {
                if ($row['key'] != '_null_')
                {
                    $slug = OliveSentiment::_getTokens($row['key']);

                    $exists = Analysis::where('group', 'sentiment-neg')->where('word', 'ILIKE', '%'.$slug[0].'%')->exists();

                    if (!$exists)
                    {
                        $data[] = $row['key'];
                    }
                }
            }

            print_r($data);
        }
    }
}
