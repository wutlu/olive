<?php

namespace App\Jobs\Elasticsearch;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Crawlers\YouTubeCrawler;

use App\Models\Option;

class CreateYouTubeIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $type;

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
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $es = new YouTubeCrawler;

        $indices = $es->indexCreate($this->type);

        if ($indices->status == 'created' || $indices->status == 'exists')
        {
            Option::where('key', implode('.', [ 'youtube', 'index', $this->type ]))->update([
                'value' => 'on'
            ]);
        }
        else
        {
            CreateYouTubeIndexJob::dispatch($this->type)->onQueue('elasticsearch')->delay(now()->addMinutes(10));
        }
    }
}
