<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;

use App\Models\Twitter\Token;
use App\Models\Twitter\StreamingKeywords;
use App\Models\Twitter\StreamingUsers;

use App\Elasticsearch\Document;

use System;

use Carbon\Carbon;

use App\Utilities\Term;

class StreamUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:stream:update {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twitter, gerçek zamanlı eylem planla.';

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
        $type = $this->option('type');

        $types = [
            'user' => 'Generate User Stream',
            'keyword' => 'Generate Keyword Stream',
            'trend' => 'Generate Trend Stream'
        ];

        if (!$type)
        {
            $type = $this->choice('What kind of a start streaming?', $types, $type);
        }

        if (array_key_exists($type, $types))
        {
            self::stream($type);
        }
        else
        {
            $this->error('Bad type.');
        }
    }

    # 
    # generate user stream
    # 
    private function stream(string $type)
    {
        switch ($type)
        {
            case 'user':
                $kquery = StreamingUsers::with('organisation')->whereNull('reason')->whereHas('organisation', function ($query) { $query->where('status', true); })->orderBy('user_id', 'ASC')->get();
                $klimit = 5000;
                $kcolumn = 'user_id';
            break;
            case 'keyword':
                $kquery = StreamingKeywords::with('organisation')->whereNull('reason')->whereHas('organisation', function ($query) { $query->where('status', true); })->orderBy('keyword', 'ASC')->get();
                $klimit = 400;
                $kcolumn = 'keyword';
            break;
            case 'trend':
                $klimit = 150;
            break;
        }

        $chunk = [];
        $chunk_id = 0;

        if ($type == 'trend')
        {
            $query = Document::search(
                [ 'trend', 'titles' ],
                'title',
                [
                    'size' => 0,
                    'query' => [
                        'bool' => [
                            'must' => [
                                [ 'match' => [ 'module' => 'twitter' ] ]
                            ],
                            'filter' => [
                                'range' => [
                                    'created_at' => [
                                        'format' => 'YYYY-MM-dd HH',
                                        'gte' => Carbon::now()->subHours(6)->format('Y-m-d H')
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'aggs' => [
                        'unique' => [
                            'terms' => [
                                'field' => 'title',
                                'size' => $klimit,
                                'order' => [
                                    '_count' => 'DESC'
                                ]
                            ]
                        ]
                    ]
                ]
            );

            if (@$query->data['aggregations']['unique']['buckets'])
            {
                $filtered = array_map(function ($q) {
                    return Term::convertAscii($q['key']);
                }, $query->data['aggregations']['unique']['buckets']);

                if (count($filtered))
                {
                    foreach (array_chunk($filtered, 50) as $query)
                    {
                        foreach ($query as $row)
                        {
                            $chunk[$row] = $row;
                        }

                        self::token($type, $chunk_id, $chunk);

                        $chunk = [];

                        $chunk_id++;
                    }
                }
            }
        }
        else
        {
            if (count($kquery))
            {
                foreach ($kquery->chunk($klimit) as $query)
                {
                    foreach ($query as $row)
                    {
                        $chunk[$row->{$kcolumn}] = $row->{$kcolumn};
                    }

                    self::token($type, $chunk_id, $chunk);

                    $chunk = [];

                    $chunk_id++;
                }
            }
        }

        if ($chunk_id == 0)
        {
            $message = 'List not found: ['.$type.']';

            $this->error($message);

            System::log(
                json_encode($message),
                'App\Console\Commands\Crawlers\Twitter\Stream::stream('.$type.')',
                10
            );
        }

        for ($i = ($chunk_id); $i <= ($chunk_id + 9); $i++)
        {
            $tmp_key = implode('_', [ $type, 'chunk', $i ]);

            $this->line($tmp_key);

            $t = Token::where('tmp_key', $tmp_key)->first();

            if (@$t)
            {
                $t->status = 'stop';
                $t->save();

                $this->info('cleaned');
            }
        }
    }

    private function token(string $type, int $chunk_id, $chunk)
    {
        $tmp_key = implode('_', [ $type, 'chunk', $chunk_id ]);

        $token = Token::where('tmp_key', $tmp_key)->first();

        if (!@$token)
        {
            $token = Token::whereNull('pid')->where('status', 'off')->orderBy('updated_at', 'ASC')->first();
        }

        if (@$token)
        {
            $this->info('followed: ['.$tmp_key.']['.count($chunk).']');

            $new_value = implode(',', $chunk);

            $token->type = ($type == 'keyword' || $type == 'trend') ? 'track' : 'follow';
            $token->tmp_key = $tmp_key;
            $token->status = $token->status == 'off' ? 'start' : ($token->status == 'restart' ? 'restart' : ((md5($token->value) == md5($new_value)) ? 'on' : 'restart'));
            $token->value = $new_value;
            $token->save();
        }
        else
        {
            $message = 'Twitter, akış için yeterli token bulunamadı.';

            $this->error('followed: ['.$tmp_key.']['.count($chunk).']');
            $this->error($message);

            System::log(
                json_encode($message),
                'App\Console\Commands\Crawlers\Twitter\Stream::stream('.$type.')',
                10
            );
        }
    }
}
