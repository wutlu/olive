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
    protected $description = 'Twitter gerçek zamanlı işlem ön planlayıcı.';

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
                $kquery = StreamingUsers::with('organisation')
                                        ->whereNull('reason')
                                        ->whereHas('organisation', function ($query) {
                                           $query->where('status', true);
                                        })
                                        ->orderBy('user_id', 'ASC');
                $klimit = 5000;
                $kcolumn = 'user_id';
            break;
            case 'keyword':
                $kquery = StreamingKeywords::with('organisation')
                                        ->whereNull('reason')
                                        ->whereHas('organisation', function ($query) {
                                           $query->where('status', true);
                                        })
                                        ->orderBy('keyword', 'ASC');
                $klimit = 400;
                $kcolumn = 'keyword';
            break;
            case 'trend':
                $klimit = 200;
            break;
        }

        $chunk = [];
        $chunk_id = 0;

        if ($type == 'trend')
        {
            $query = Document::list(
                [ 'twitter', 'trends' ],
                'trend',
                [
                    'size' => 0,
                    'query' => [
                        'bool' => [
                            'filter' => [
                                'range' => [
                                    'created_at' => [
                                        'format' => 'YYYY-MM-dd',
                                        'gte' => Carbon::now()->subDays(1)->format('Y-m-d')
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
                    $chunk = $filtered;
                    $chunk_id = 1;
                }
            }
        }
        else
        {
            if ($kquery->count())
            {
                foreach ($kquery->get()->chunk($klimit) as $query)
                {
                    $chunk_id++;

                    foreach ($query as $row)
                    {
                        $chunk[$row->{$kcolumn}] = $row->{$kcolumn};
                    }
                }
            }
        }

        if (count($chunk))
        {
            self::token($type, $chunk_id, $chunk);
        }
        else
        {
            $message = 'List not found: ['.$type.']';

            $this->error($message);

            System::log(
                json_encode($message),
                'App\Console\Commands\Crawlers\Twitter\Stream::stream('.$type.')',
                10
            );
        }

        for ($i = ($chunk_id+1); $i <= ($chunk_id + 9); $i++)
        {
            $tmp_key = implode('_', [ $type, 'chunk', $i ]);

            $this->line($tmp_key);

            $t = Token::where('tmp_key', $tmp_key);

            if ($t->exists())
            {
                $t = $t->first();

                $t->status = 'stop';
                $t->save();

                $this->info('cleaned');
            }
        }
    }

    private function token(string $type, int $chunk_id, $chunk)
    {
        $tmp_key = implode('_', [ $type, 'chunk', $chunk_id ]);

        $this->line($tmp_key);

        $token = Token::where('tmp_key', $tmp_key);

        if (!$token->exists())
        {
            $token = Token::whereNull('pid')->where('status', 'off')->orderBy('updated_at', 'ASC');
        }

        $this->info('followed: ['.count($chunk).']');

        if ($token->exists())
        {
            $token = $token->first();

            $new_value = implode(',', $chunk);

            $token->type = ($type == 'keyword' || $type == 'trend') ? 'track' : 'follow';
            $token->tmp_key = $tmp_key;
            $token->status = $token->status == 'off' ? 'start' : ($token->status == 'restart' ? 'restart' : ((md5($token->value) == md5($new_value)) ? 'on' : 'restart'));
            $token->value = $new_value;
            $token->save();
        }
        else
        {
            $message = 'Twitter akış için yeterli token bulunamadı.';

            $this->error($message);

            System::log(
                json_encode($message),
                'App\Console\Commands\Crawlers\Twitter\Stream::stream('.$type.')',
                10
            );
        }
    }
}
