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
                'total_fields_limit' => config('database.elasticserach.google.search.settings.total_fields_limit'),
                'number_of_shards' => config('database.elasticserach.google.search.settings.number_of_shards'),
                'number_of_replicas' => config('database.elasticserach.google.search.settings.number_of_replicas'),
                'refresh_interval' => config('database.elasticserach.google.search.settings.refresh_interval')
            ]
        );
    }
}
