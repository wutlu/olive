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
    public $value;

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
    public function __construct(string $type, string $value = '')
    {
        $this->type = $type;
        $this->value = $value;
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
            Option::where('key', implode('.', [ 'youtube', 'index', $this->value ? 'comments' : $this->type ]))->update([
                'value' => $this->value ? $this->value : $this->type
            ]);
        }
        else
        {
            CreateYouTubeIndexJob::dispatch($this->type, $this->value)->onQueue('error-crawler')->delay(now()->addMinutes(10));
        }
    }
}
