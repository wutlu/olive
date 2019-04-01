<?php

namespace App\Console\Commands\Crawlers\Media;

use Illuminate\Console\Command;

use App\Models\Crawlers\MediaCrawler;

use App\Models\Crawlers\Host as HostModel;

use System;

class Host extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:host';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Medya siteleri için dns tespiti.';

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
        $crawlers = MediaCrawler::where('status', true)->get();

        if (count($crawlers))
        {
            foreach ($crawlers as $crawler)
            {
                try
                {
                    $this->line($crawler->site);

                    $ip = gethostbyname(str_replace([ 'https://', 'http://', 'www.' ], '', $crawler->site));

                    $this->info($ip);

                    HostModel::firstOrCreate(
                        [
                            'site' => $crawler->site,
                            'ip_address' => $ip
                        ]
                    );
                }
                catch (\Exception $e)
                {
                    $this->error($e->getMessage());

                    System::log(
                        json_encode(
                            $e->getMessage()
                        ),
                        'App\Console\Commands\Crawlers\Media\Host::create('.$crawler->id.')',
                        10
                    );
                }
            }
        }
    }
}
