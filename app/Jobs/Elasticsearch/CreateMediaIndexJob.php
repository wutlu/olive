<?php

namespace App\Jobs\Elasticsearch;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Crawlers\MediaCrawler;

class CreateMediaIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id;

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
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $es = MediaCrawler::where('id', $this->id)->first();

        if (@$es)
        {
            $indices = $es->indexCreate();

            if ($indices->status == 'created' || $indices->status == 'exists')
            {
                $es->elasticsearch_index = true;
            }
            else
            {
                CreateMediaIndexJob::dispatch($this->id)->onQueue('elasticsearch')->delay(now()->addMinutes(10));

                $es->elasticsearch_index = false;
            }

            $es->save();
        }
    }
}
