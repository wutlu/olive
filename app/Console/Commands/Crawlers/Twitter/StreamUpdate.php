<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis as RedisCache;

use App\Models\Twitter\Token;
use App\Models\Twitter\StreamingKeywords;
use App\Models\Twitter\StreamingUsers;

use App\Elasticsearch\Document;

use App\Models\TrendArchive;

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
                $klimit = 5000;
                $kcolumn = 'user_id';

                $kquery = new StreamingUsers;
                $kquery = $kquery::with('organisation')
                                 ->whereNull('reason')
                                 ->whereHas('organisation', function ($query) {
                                     $query->where('status', true);
                                 })
                                 ->orderBy('user_id', 'ASC')
                                 ->get();
            break;
            case 'keyword':
                $klimit = 100;
                $kcolumn = 'keyword';

                $kquery = new StreamingKeywords;
                $kquery = $kquery::with('organisation')
                                 ->whereNull('reason')
                                 ->whereHas('organisation', function ($query) {
                                      $query->where('status', true);
                                  })
                                 ->orderBy('keyword', 'ASC')
                                 ->get();
            break;
            case 'trend':
                $klimit = 50;
            break;
        }

        $chunk = [];
        $chunk_id = 0;

        if ($type == 'trend')
        {
            $alias = config('system.db.alias');

            $last_key = TrendArchive::where('module', 'twitter_hashtag')->orderBy('created_at', 'DESC')->first();
            $last_key->disabled_mutator = true;

            if (@$last_key)
            {
                $query = Document::search(
                    [
                        'trend',
                        'titles'
                    ],
                    'title',
                    [
                        'size' => 50,
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [ 'match' => [ 'module' => 'twitter_hashtag' ] ],
                                    [ 'match' => [ 'group' => $last_key->group ] ]
                                ]
                            ]
                        ],
                        '_source' => [ 'data.key' ]
                    ]
                );

                if ($query->status == 'ok')
                {
                    if ($query->data['hits']['hits'])
                    {
                        $filtered = array_map(function ($q) {
                            return Term::convertAscii($q['_source']['data']['key']);
                        }, $query->data['hits']['hits']);

                        if (count($filtered))
                        {
                            foreach (array_chunk($filtered, $klimit) as $query)
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

            $token->tmp_key = $tmp_key;
            $token->type    = ($type == 'keyword' || $type == 'trend') ? 'track' : 'follow';
            $token->status  = $token->status == 'off' ? 'start' : ($token->status == 'restart' ? 'restart' : ((md5($token->value) == md5($new_value)) ? 'on' : 'restart'));
            $token->value   = $new_value;
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
