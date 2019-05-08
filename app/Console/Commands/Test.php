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
        $sentiment = new Sentiment;
        $sentiment->engine('sentiment');

        $data = [];

        foreach (explode(PHP_EOL, 'Merhaba nasıl gidiyor iyiyim vs.') as $string)
        {
            $data[] = [
                'text' => $string,
                'data' => $sentiment->score($string)
            ];
        }

        print_r($data);
    }
}
