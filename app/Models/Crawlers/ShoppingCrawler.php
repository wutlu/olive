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
        'google_search_url',
        'url_pattern',

		'selector_title',
		'selector_description',
		'selector_categories',
		'selector_address',
		'selector_ul',
		'selector_ul_li',
		'selector_ul_li_key',
		'selector_ul_li_val',
		'selector_seller_name',
		'selector_selles_phones',

        'off_limit',
        'control_interval',

        'status',
        'error_count'
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
                        'id' => [
                            'type' => 'keyword'
                        ],
                        'site_id' => [
                            'type' => 'integer'
                        ],
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
                        'seller' => [
                        	'properties' => [
                        		'name' => [
                        			'type' => 'keyword'
                        		],
                        		'phones' => [
                        			'type' => 'nested'
                        		]
                        	]
                        ],
                        'categories' => [
                        	'type' => 'nested'
                        ],
                        'types' => [
                        	'type' => 'nested'
                        ],
                        'address' => [
                        	'type' => 'nested'
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
                            'type' => 'text',
                            'index' => false
                        ],
                        'status' => [
                            'type' => 'keyword'
                        ],
                        'message' => [
                            'type' => 'text',
                            'index' => false
                        ]
                    ]
                ]
            ],
            [
                'total_fields_limit' => 40,
                'number_of_shards' => 2,
                'number_of_replicas' => 1,
                'refresh_interval' => '5s'
            ]
        );
    }

    # index stats
    public function stats()
    {
        return Indices::stats([ 'shopping', $this->id ]);
    }
}
