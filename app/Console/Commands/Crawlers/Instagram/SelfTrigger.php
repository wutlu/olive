<?php

namespace App\Console\Commands\Crawlers\Instagram;

use Illuminate\Console\Command;

use App\Models\Crawlers\Instagram\Selves;
use App\Instagram;

use DB;

use App\Jobs\Crawlers\Instagram\SelfJob;

class SelfTrigger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:self:trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Belirlenen instagram bot görevlerini tetikler.';

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
        $selves = Selves::where('status', true)
                        ->where(
                            'control_date',
                            '<=',
                            DB::raw("NOW() - INTERVAL '1 minutes' * control_interval")
                        )
                        ->orderBy('control_date', 'ASC')
                        ->get();

        $links = [];

        if (@$selves)
        {
            foreach ($selves as $self)
            {
                $links[md5($self->url)] = $self;
            }
        }

        if (count($links))
        {
            foreach ($links as $self)
            {
                $this->info($self->url);

                SelfJob::dispatch($self)->onQueue('power-crawler')->delay(now()->addSeconds(rand(1, 4)));
            }
        }
    }
}
