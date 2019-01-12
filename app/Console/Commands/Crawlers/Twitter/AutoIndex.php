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
        $carbon = new Carbon;

        $last_month = Option::where('key', 'twitter.index.tweets')->first();

        if (@$last_month)
        {
            $last_month = $carbon->createFromFormat('Y.m', $last_month->value)->format('Y.m');

            while ($last_month <= date('Y.m', strtotime('+1 month')))
            {
                $index_name = implode('-', [ 'tweets', $last_month ]);

                CreateTwitterIndexJob::dispatch($index_name, $last_month)->onQueue('elasticsearch');

                echo $this->info($index_name);

                $last_month = $carbon->createFromFormat('Y.m', $last_month)->addMonth()->format('Y.m');
            }
        }
        else
        {
            $this->error('Ayar değeri bulunamadı.');
        }
    }
}
