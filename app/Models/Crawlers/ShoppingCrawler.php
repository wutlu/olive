<?php

namespace App\Models\Crawlers;

use Illuminate\Database\Eloquent\Model;
use App\Elasticsearch\Indices;

class ShoppingCrawler extends Model
{
    protected $table = 'shopping_crawlers';
    protected $fillable = [
        'name',
        'site',
        'google_search_query',
        'google_time',
        'google_max_page',
        'url_pattern',

		'selector_title',
		'selector_description',
		'selector_address',
		'selector_breadcrumb',
		'selector_seller_name',
		'selector_seller_phone',
        'selector_price',

        'off_limit',
        'control_interval',

        'status',
        'error_count',
        'proxy',
    ];

    # index crate
    public function indexCreate()
    {
        return Indices::create(
            [
                'shopping', $this->id
            ],
            [
                'product' => [
                    'properties' => [
                        'id' => [ 'type' => 'keyword' ],
                        'site_id' => [ 'type' => 'integer' ],
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'turkish',
                            'fielddata' => true
                        ],
                        'description' => [
                            'type' => 'text',
                            'analyzer' => 'turkish',
                            'fielddata' => true
                        ],
                        'sentiment' => [
                            'properties' => [
                                'neg' => [ 'type' => 'float' ],
                                'pos' => [ 'type' => 'float' ],
                                'neu' => [ 'type' => 'float' ]
                            ]
                        ],
                        'price' => [
                            'properties' => [
                                'currency' => [ 'type' => 'keyword' ],
                                'amount' => [ 'type' => 'long' ]
                            ]
                        ],
                        'seller' => [
                        	'properties' => [
                        		'gender' => [ 'type' => 'keyword' ],
                                'name' => [ 'type' => 'keyword' ],
                        		'phones' => [
                        			'type' => 'nested',
                                    'properties' => [
                                        'phone' => [ 'type' => 'keyword' ]
                                    ]
                        		]
                        	]
                        ],
                        'breadcrumb' => [
                        	'type' => 'nested',
                            'properties' => [
                                'segment' => [ 'type' => 'keyword' ]
                            ]
                        ],
                        'address' => [
                        	'type' => 'nested',
                            'properties' => [
                                'segment' => [ 'type' => 'keyword' ]
                            ]
                        ],
                        'created_at' => [
                            'type' => 'date',
                            'format' => 'YYYY-MM-dd HH:mm:ss'
                        ],
                        'called_at' => [
                            'type' => 'date',
                            'format' => 'YYYY-MM-dd HH:mm:ss'
                        ],
                        'url' => [
                            'type' => 'keyword',
                            'index' => false
                        ],
                        'status' => [ 'type' => 'keyword' ],
                        'message' => [
                            'type' => 'text',
                            'index' => false
                        ]
                    ]
                ]
            ],
            [
                'total_fields_limit' => config('database.elasticsearch.shopping.product.settings.total_fields_limit'),
                'number_of_shards' => config('database.elasticsearch.shopping.product.settings.number_of_shards'),
                'number_of_replicas' => config('database.elasticsearch.shopping.product.settings.number_of_replicas'),
                'refresh_interval' => config('database.elasticsearch.shopping.product.settings.refresh_interval')
            ]
        );
    }

    # index stats
    public function stats()
    {
        return Indices::stats([ 'shopping', $this->id ]);
    }
}
