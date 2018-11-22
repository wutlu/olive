<?php

namespace App\Models\Crawlers;

use App\Elasticsearch\Indices;

class GoogleCrawler
{
    # index crate
    public function indexCreate()
    {
        return Indices::create(
            [ 'google', 'search' ],
            [
                'search' => [
                    'properties' => [
                        'id' => [
                            'type' => 'keyword'
                        ],
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'turkish',
                            'fielddata' => true
                        ],
                        'approx_traffic' => [
                            'type' => 'integer'
                        ],
                        'created_at' => [
                            'type' => 'date',
                            'format' => 'YYYY-MM-dd HH:mm:ss'
                        ],
                        'called_at' => [
                            'type' => 'date',
                            'format' => 'YYYY-MM-dd HH:mm:ss'
                        ]
                    ]
                ]
            ],
            [
                'total_fields_limit' => 22,
                'number_of_shards' => 2,
                'number_of_replicas' => 0,
                'refresh_interval' => '10s'
            ]
        );
    }
}
