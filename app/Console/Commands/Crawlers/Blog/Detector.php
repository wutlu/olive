<?php

namespace App\Console\Commands\Crawlers\Blog;

use Illuminate\Console\Command;

use App\Jobs\Crawlers\Blog\DetectorJob;

use App\Models\Crawlers\BlogCrawler;

use Carbon\Carbon;

use DB;

class Detector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:detector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Blog kaynaklarını tespit eder.';

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
        $crawlers = BlogCrawler::where('status', true)
                               ->where(
                                   'control_date',
                                   '<=',
                                   DB::raw("NOW() - INTERVAL '1 minutes' * control_interval")
                               )
                               ->orderBy('control_date', 'ASC')
                               ->get();

        if (@$crawlers)
        {
            foreach ($crawlers as $crawler)
            {
                $this->info($crawler->name);

                DetectorJob::dispatch($crawler)->onQueue('crawler');
            }
        }
    }
}
