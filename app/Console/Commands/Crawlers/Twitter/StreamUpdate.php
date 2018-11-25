<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;

use App\Models\Twitter\Token;
use App\Models\Twitter\StreamingKeywords;
use App\Models\Twitter\StreamingUsers;

use System;

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
    protected $description = 'Twitter gerçek zamanlı işlem derleyici.';

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
            self::{$type}();
        }
        else
        {
            $this->error('Bad type.');
        }
    }

    # 
    # generate user stream
    # 
    private function user()
    {
        $kquery = StreamingUsers::with('organisation')
                                ->whereNull('reasons')
                                ->whereHas('organisation', function ($query) {
                                   $query->where('status', true);
                                })
                                ->distinct();

        if ($kquery->count())
        {
            $chunk_id = 0;

            foreach ($kquery->get()->chunk(400) as $query)
            {
                $chunk = [];
                $chunk_id++;
                $tmp_key = implode('_', [ 'chunk', $chunk_id ]);

                $this->line($tmp_key);

                foreach ($query as $row)
                {
                    $chunk[] = $row->user_id;
                }

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

                    $token->type = 'follow';
                    $token->tmp_key = $tmp_key;
                    $token->value = $new_value;
                    $token->status = (md5($token->value) == md5($new_value)) ? 'on' : 'restart';
                    $token->save();
                }
                else
                {
                    $message = 'Twitter kullanıcı akışı için yeterli token bulunamadı.';

                    $this->error($message);

                    System::log(
                        json_encode($message),
                        'App\Console\Commands\Crawlers\Twitter\Stream::user()',
                        10
                    );
                }
            }

            for ($i = ($chunk_id+1); $i <= ($chunk_id + 5); $i++)
            {
                $tmp_key = implode('_', [ 'chunk', $i ]);

                $this->line($tmp_key);

                $t = Token::where('tmp_key', $tmp_key);

                if ($t->exists())
                {
                    $t = $t->first();

                    $t->status = 'kill';
                    $t->save();

                    $this->info('cleaned');
                }
            }
        }
        else
        {
            $this->error('User list not found.');
        }
    }

    # 
    # generate keyword stream
    # 
    private function keyword()
    {
        $this->info('keyword');
    }

    # 
    # generate trend stream
    # 
    private function trend()
    {
        $this->info('trend');
    }
}
