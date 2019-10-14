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

use App\Olive\Gender;

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
            $gender = new Gender;
            $gender->loadNames();

            $sentiment = new Sentiment;
            $sentiment->engine('sentiment');

            $item = Crawler::productDetection($crawler->site, $this->data['url'], [
				'title' => $crawler->selector_title,
				'description' => $crawler->selector_description,
				'address' => $crawler->selector_address,
				'breadcrumb' => $crawler->selector_breadcrumb,
				'seller_name' => $crawler->selector_seller_name,
                'seller_phones' => $crawler->selector_seller_phones,
				'price' => $crawler->selector_price,
            ], $crawler->proxy);

            if ($item->status == 'ok')
            {
            	$params = [
                    'title' => $item->data['title'],
                    'created_at' => $item->data['created_at'],
                    'called_at' => date('Y-m-d H:i:s'),
                    'status' => 'ok'
                ];

                $sources = [
                    'ctx._source.title = params.title;',
                    'ctx._source.created_at = params.created_at;',
                    'ctx._source.called_at = params.called_at;',
                    'ctx._source.status = params.status;',
                ];

                if (@$item->data['seller_name'])
                {
                    $params['seller'] = [
                        'name' => $item->data['seller_name'],
                        'gender' => $gender->detector([ $item->data['seller_name'] ]),
                    ];
                    $sources[] = 'ctx._source.seller = params.seller;';
                }

                if (@$item->data['price'])
                {
                    $params['price'] = $item->data['price'];
                    $sources[] = 'ctx._source.price = params.price;';
                }

                if (@$item->data['address'])
                {
                    $params['address'] = array_map(function ($value) {
                        return [ 'segment' => $value ];
                    }, $item->data['address']);
                    $sources[] = 'ctx._source.address = params.address;';
                }

                if (@$item->data['breadcrumb'])
                {
                    $params['breadcrumb'] = array_map(function ($value) {
                        return [ 'segment' => $value ];
                    }, $item->data['breadcrumb']);
                    $sources[] = 'ctx._source.breadcrumb = params.breadcrumb;';
                }

                if (@$item->data['description'])
                {
                    $params['description'] = $item->data['description'];
                    $params['sentiment'] = $sentiment->score($item->data['description']);

                    $sources[] = 'ctx._source.description = params.description;';
                    $sources[] = 'ctx._source.sentiment = params.sentiment;';

                    $category = new Sentiment;
                    $category->engine('category');

                    $category_name = $category->net($item->data['description'], 'category');

                    if ($category_name)
                    {
                        $params['category'] = $category_name;
                        $sources[] = 'ctx._source.category = params.category;';
                    }
                }

                if (@$item->data['seller_phones'])
                {
                    $params['seller']['phones'] = array_map(function ($value) {
                        return [ 'phone' => $value ];
                    }, $item->data['seller_phones']);
                }

                $upsert = Document::patch([ 'shopping', $crawler->id ], 'product', $this->data['id'], [
                    'script' => [
                        'source' => implode(PHP_EOL, $sources),
                        'params' => $params
                    ]
                ]);

                # ES hatalarını 10 dakika sonra tekrar dene.
                if ($upsert->status == 'err')
                {
                    TakerJob::dispatch($this->data)->onQueue('error-crawler')->delay(now()->addMinutes(10));
                }

                # Hata varken sorunsuz işlem gerçekleştirildiğinde hata alanını sıfırla.
                if ($crawler->error_count > 0)
                {
                    $crawler->update([ 'error_count' => 0 ]);
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
                        Mail::queue(new ServerAlertMail($crawler->name.' E-ticaret Botu [DURDU]', json_encode($item->error_reasons)));
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
