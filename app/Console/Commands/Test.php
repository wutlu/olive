<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Geo\States;
use App\Models\Crawlers\MediaCrawler;
use Term;

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
        $states = States::where('country_id', 223)->get();

        $count = 0;

        foreach ($states as $state)
        {
            $crawlers = MediaCrawler::orWhere('name', 'ilike', '%'.str_slug($state->name).'%')
                                    ->orWhere('name', 'ilike', '%'.$state->name.'%')
                                    ->orWhere(
                                        'site',
                                        'ilike',
                                        '%'.str_replace(
                                            [
                                                'sanliurfa',
                                                'kahramanmaras',
                                                'gaziantep',
                                            ],
                                            [
                                                'urfa',
                                                'maras',
                                                'antep',
                                            ],
                                            str_slug($state->name)
                                        ).'%'
                                    )
                                    ->get();
            if (count($crawlers))
            {
                $this->info($state->name);

                foreach ($crawlers as $crawler)
                {
                    $count++;

                    $this->info($crawler->name);

                    $crawler->state = $state->name;
                    $crawler->update();
                }

                $this->info('---');
            }
        }

        $this->info($count);
    }
}
