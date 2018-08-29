<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;

use App\Models\Ticket;

class MonitorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->key)
        {
            case 'monitor:ticket:count':
                Redis::set($this->key, Ticket::where('status', 'open')->count());
            break;
        }
        
    }
}
