<?php

namespace App\Console\Commands\Crawlers\Media;

use Illuminate\Console\Command;

use App\Jobs\Crawlers\Media\DetectorJob;

use App\Models\Crawlers\MediaCrawler;

use Carbon\Carbon;

use DB;

class Detector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:detector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Medya kaynaklarını tespit et.';

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
        $crawlers = MediaCrawler::where('status', true)
                                ->where('control_date', '<=', DB::raw("NOW() - INTERVAL '1 minutes' * control_interval"))
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
