<?php

namespace App\Models\Crawlers;

use Illuminate\Database\Eloquent\Model;
use App\Elasticsearch\Indices;

class MediaCrawler extends Model
{
    protected $table = 'media_crawlers';
    protected $fillable = [
        'name',
        'site',
        'base',
        'url_pattern',
        'selector_title',
        'selector_description',

        'off_limit',
        'control_interval'
    ];

    # index crate
    public function indexCreate()
    {
        return Indices::indexCreate(
            [ 'articles', $this->id ],
            [
                'article' => [
                    'properties' => [
                        'id' => [
                            'type' => 'keyword'
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
                        ]
                    ]
                ]
            ],
            [
                'total_fields_limit' => 20,
                'number_of_shards' => 4,
                'number_of_replicas' => 1,
                'refresh_interval' => '10s'
            ]
        );
    }

    # index stats
    public function indexStats()
    {
        return Indices::indexStats([ 'articles', $this->id ]);
    }
}
