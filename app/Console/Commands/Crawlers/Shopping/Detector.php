<?php

namespace App\Console\Commands\Crawlers\Shopping;

use Illuminate\Console\Command;

use App\Jobs\Crawlers\Shopping\DetectorJob;

use App\Models\Crawlers\ShoppingCrawler;

use Carbon\Carbon;

class Detector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopping:detector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ürün bağlantı tespit edici.';

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
        $crawlers = ShoppingCrawler::where([
            'status' => true
        ])
        ->orderBy('control_date', 'ASC')
        ->get();

        if (@$crawlers)
        {
            foreach ($crawlers as $crawler)
            {
                if (Carbon::now() > Carbon::createFromFormat('Y-m-d H:i:s', $crawler->control_date)->addMinutes($crawler->control_interval))
                {
                    $this->info($crawler->name);

                    DetectorJob::dispatch($crawler)->onQueue('crawler');
                }
            }
        }
    }
}
