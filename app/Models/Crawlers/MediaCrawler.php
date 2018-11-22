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
        'control_interval',

        'status',
        'error_count'
    ];

    # index crate
    public function indexCreate()
    {
        return Indices::create(
            [
                'articles', $this->id
            ],
            [
                'article' => [
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
                        ],
                        'sentiment' => [ 'type' => 'short' ]
                    ]
                ]
            ],
            [
                'total_fields_limit' => 20,
                'number_of_shards' => 2,
                'number_of_replicas' => 0,
                'refresh_interval' => '5s'
            ]
        );
    }

    # index stats
    public function stats()
    {
        return Indices::stats([ 'articles', $this->id ]);
    }
}
