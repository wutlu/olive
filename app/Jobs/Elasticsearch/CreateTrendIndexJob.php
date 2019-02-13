<?php

namespace App\Jobs\Elasticsearch;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Trend;
use App\Models\Option;

class CreateTrendIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $es = new Trend;

        $indices = $es->indexCreate();

        if ($indices->status == 'created' || $indices->status == 'exists')
        {
            Option::where('key', 'trend.index')->update([
                'value' => 'on'
            ]);
        }
        else
        {
            CreateTrendIndexJob::dispatch()->onQueue('error-crawler')->delay(now()->addMinutes(10));
        }
    }
}
