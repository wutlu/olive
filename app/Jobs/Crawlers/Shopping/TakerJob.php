<?php

namespace App\Jobs\Crawlers\Shopping;

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
use Sentiment;

class TakerJob implements ShouldQueue
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
        $crawler = ShoppingCrawler::where('id', $this->data['site_id'])->first();

        if (@$crawler)
        {
            $sentiment = new Sentiment;

            $item = Crawler::productDetection($crawler->site, $this->data['url'], [
				'title' => $crawler->selector_title,
				'description' => $crawler->selector_description,
				'address' => $crawler->selector_address,
				'breadcrumb' => $crawler->selector_breadcrumb,
				'seller_name' => $crawler->selector_seller_name,
                'seller_phones' => $crawler->selector_seller_phones,
				'price' => $crawler->selector_price
            ]);

            if ($item->status == 'ok')
            {
            	$params = [
                    'title' => $item->data['title'],
                    'created_at' => $item->data['created_at'],
                    'breadcrumb' => array_map(function ($value) {
				        return [ 'segment' => $value ];
				    }, $item->data['breadcrumb']),
                    'address' => array_map(function ($value) {
				        return [ 'segment' => $value ];
				    }, $item->data['address']),
                    'seller' => [
                    	'name' => $item->data['seller_name']
                    ],
                    'price' => $item->data['price'],
                    'called_at' => date('Y-m-d H:i:s'),
                    'status' => 'ok'
                ];

                $source = [
                    'ctx._source.title = params.title;',
                    'ctx._source.breadcrumb = params.breadcrumb;',
                    'ctx._source.address = params.address;',
                    'ctx._source.seller = params.seller;',
                    'ctx._source.created_at = params.created_at;',
                    'ctx._source.called_at = params.called_at;',
                    'ctx._source.status = params.status;',
                    'ctx._source.price = params.price;',
                ];

                if (@$item->data['description'])
                {
                    $params['description'] = $item->data['description'];
                    $params['sentiment'] = $sentiment->score($item->data['description']);
                    $source[] = 'ctx._source.description = params.description;';
                    $source[] = 'ctx._source.sentiment = params.sentiment;';
                }

                if ($item->data['seller_phones'])
                {
                    $params['seller']['phones'] = array_map(function ($value) {
                        return [ 'phone' => $value ];
                    }, $item->data['seller_phones']);
                }

                $upsert = Document::patch([ 'shopping', $crawler->id ], 'product', $this->data['id'], [
                    'script' => [
                        'source' => implode(PHP_EOL, $source),
                        'params' => $params
                    ]
                ]);

                # Hata varken sorunsuz işlem gerçekleştirildiğinde hata alanını sıfırla.
                if ($crawler->error_count > 0)
                {
                    $crawler->update([ 'error_count' => 0 ]);
                }

                # ES hatalarını 10 dakika sonra tekrar dene.
                if ($upsert->status == 'err')
                {
                    TakerJob::dispatch($this->data)->onQueue('crawler')->delay(now()->addMinutes(10));
                }
            }
            else if ($item->status == 'err' || $item->status == 'failed')
            {
                $insert = Document::patch([ 'shopping', $crawler->id ], 'product', $this->data['id'], [
                    'doc' => [
                        'called_at' => date('Y-m-d H:i:s'),
                        'status' => $this->data['status'] == 'buffer' ? 'again' : ($this->data['status'] == 'again' ? 'last_time' : 'failed'),
                        'message' => $item->status == 'err' ? json_encode($item->error_reasons) : 'not_found'
                    ]
                ]);

                $crawler->error_count = $crawler->error_count+1;

                if ($crawler->error_count >= $crawler->off_limit)
                {
                    System::log(json_encode($item->error_reasons), 'App\Jobs\Crawlers\Shopping\TakerJob::handle(int '.$crawler->id.')', 10);

                    if ($crawler->status == true)
                    {
                        Mail::queue(new ServerAlertMail($crawler->name.' Alışveriş Botu [DURDU]', json_encode($item->error_reasons)));
                    }

                    $crawler->off_reason = json_encode($item->error_reasons);
                    $crawler->status = false;
                    $crawler->test = false;
                }

                $crawler->save();
            }
        }
        else
        {
            $insert = Indices::drop([ 'shopping', $this->data['site_id'] ]);
        }
    }
}
