<?php

namespace App\Jobs\Elasticsearch;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Crawlers\TwitterCrawler;

use App\Models\Option;

class CreateTwitterIndexJob implements ShouldQueue
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
        $es = new TwitterCrawler;

        $indices = $es->indexCreate($this->type);

        if ($indices->status == 'created' || $indices->status == 'exists')
        {
            Option::where('key', implode('.', [ 'twitter', 'index', $this->value ? 'tweets' : $this->type ]))->update([
                'value' => $this->value ? $this->value : $this->type
            ]);
        }
        else
        {
            CreateTwitterIndexJob::dispatch($this->type, $this->value)->onQueue('elasticsearch')->delay(now()->addMinutes(10));
        }
    }
}
