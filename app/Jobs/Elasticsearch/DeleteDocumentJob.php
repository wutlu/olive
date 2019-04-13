<?php

namespace App\Jobs\Elasticsearch;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Elasticsearch\Document;

use System;

class DeleteDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $name;
    public $type;
    public $body;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name, string $type, array $body)
    {
        $this->name = $name;
        $this->type = $type;
        $this->body = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try
        {
            Document::deleteByQuery($this->name, $this->type, $this->body);
        }
        catch (\Exception $e)
        {
            System::log(
                $e->getMessage(),
                'App\Jobs\Elasticsearch\DeleteDocumentJob::handle('.json_encode($this->name).', '.$this->type.')',
                10
            );
        }
    }
}
