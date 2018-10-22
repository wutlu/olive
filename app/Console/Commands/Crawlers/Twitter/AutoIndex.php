<?php

namespace App\Console\Commands\Crawlers\Twitter;

use Illuminate\Console\Command;

use App\Models\Option;
use Carbon\Carbon;

use App\Jobs\Elasticsearch\CreateTwitterIndexJob;

class AutoIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitter:auto_index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tweet indexlerini Tweetler alınmadan oluşturur.';

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
        $last_month = Option::where('key', 'twitter.index.tweets')->first();

        if (@$last_month)
        {
            $last_month = Carbon::createFromFormat('Y.m', $last_month->value)->format('Y.m');

            while ($last_month <= date('Y.m'))
            {
                $last_month = Carbon::createFromFormat('Y.m', $last_month)->addMonth()->format('Y.m');

                $index_name = implode('-', [ 'tweets', $last_month ]);

                CreateTwitterIndexJob::dispatch($index_name, $last_month)->onQueue('elasticsearch');

                echo $this->info($index_name);
            }
        }
        else
        {
            $this->error('Ayar değeri bulunamadı.');
        }
    }
}
