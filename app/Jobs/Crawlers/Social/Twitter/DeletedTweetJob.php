<?php

namespace App\Jobs\Crawlers\Social\Twitter;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Crawlers\ShoppingCrawler;

use App\Utilities\Crawler;
use App\Elasticsearch\Document;
use App\Elasticsearch\Indices;

use Mail;
use App\Mail\ServerAlertMail;

use System;

class DeletedTweetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    	$timestamp = date('Y-m-d H:i:s', $this->data['timestamp_ms'] / 1000);

    	$doc = Document::list([ 'twitter', 'tweets', '*' ], 'tweet', [
    		'query' => [
	    		'match' => [
	    			'id' => $this->data['status']['id_str']
	    		]
    		]
    	]);

    	if (@$doc->data['hits']['hits'][0])
    	{
    		$date = strtotime($doc->data['hits']['hits'][0]['_source']['created_at']);

	        $update = Document::patch([ 'twitter', 'tweets', date('Y.m', $date) ], 'tweet', $this->data['status']['id_str'], [
	            'doc' => [
	                'deleted_at' => date('Y-m-d H:i:s')
	            ]
	        ]);
    	}
    }
}
