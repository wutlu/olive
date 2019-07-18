<?php

namespace App\Jobs\Crawlers\Instagram;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Crawlers\Instagram\Selves;

class SelfJob// implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $self;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Selves $self)
    {
        $this->self = $self;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        print_r($this->self->toArray());
    }
}
