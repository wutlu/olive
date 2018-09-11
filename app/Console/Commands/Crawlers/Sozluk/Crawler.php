<?php

namespace App\Console\Commands\Crawlers\Sozluk;

use Illuminate\Console\Command;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

use Elasticsearch\ClientBuilder;

use App\Models\Crawlers\SozlukCrawler;

use App\Utilities\Crawler as CrawlerUtility;

use App\Jobs\Elasticsearch\BulkInsertJob;

use System;
use Mail;
use App\Mail\ServerAlertMail;

class Crawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sozluk:crawler {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sözlük entry toplayıcısı.';

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
    	$sozluk = SozlukCrawler::where('id', $this->argument('id'))->where('status', true)->first();

    	if (@$sozluk)
    	{
    		$i = 1;

    		$stream = true;
    		$errors = [];
    		$entry_id = $sozluk->last_id;

    		$chunk = [ 'body' => [] ];

    		while ($stream)
    		{
				$item = CrawlerUtility::entryDetection(
				    $sozluk->site,
				    $sozluk->url_pattern,
				    $entry_id,
				    $sozluk->selector_title,
				    $sozluk->selector_entry,
				    $sozluk->selector_author
				);

				if ($item->status == 'ok')
				{
	                $chunk['body'][] = [
	                    'create' => [
	                        '_index' => Indices::name([ 'sozluk', $sozluk->id ]),
	                        '_type' => 'entry',
	                        '_id' => $entry_id
	                    ]
	                ];

	                $chunk['body'][] = [
	                    'id' => $entry_id,

	                    'url' => $item->page,
						'group_name' => $item->group_name,

						'title' => $item->data['title'],
						'entry' => $item->data['entry'],
						'author' => $item->data['author'],

						'created_at' => $item->data['created_at'],
	                    'called_at' => date('Y-m-d H:i:s'),

	                    'site_id' => $sozluk->id
	                ];

	                $errors = [];

	                $sozluk->error_count = 0;
	                $sozluk->last_id = $entry_id;

	                $this->info($entry_id);
                }
                else
                {
                	$sozluk->error_count = $sozluk->error_count+1;
    				$errors[] = $item->error_reasons;

                	$this->error($entry_id.' - '.count($errors));
                }

                /* ---- */

    			if (count($errors) >= $sozluk->max_attempt || $i >= 1000)
    			{
    				if ($sozluk->error_count >= $sozluk->off_limit)
    				{
    					$sozluk->status = false;
    					$sozluk->test = false;
    					$sozluk->off_reason = json_encode($errors);

                        System::log(
                            $sozluk->off_reason,
                            'App\Console\Commands\Crawlers\Sozluk\Crawler::handle(int '.$sozluk->id.')',
                            10
                        );

                        Mail::queue(new ServerAlertMail($sozluk->name.' Sözlük Botu [DURDU]', $sozluk->off_reason));
    				}

    				$sozluk->save();

    				BulkInsertJob::dispatch($chunk)->onQueue('elasticsearch');

    				$stream = false;
    			}
    			else
    			{
	    			$entry_id++;
    			}

    			$i++;
    		}
    	}
    }
}
