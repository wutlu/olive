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
        'error_count',
        'proxy',
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
                        'sentiment' => [
                            'properties' => [
                                'neg' => [ 'type' => 'float' ],
                                'pos' => [ 'type' => 'float' ],
                                'neu' => [ 'type' => 'float' ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'total_fields_limit' => config('database.elasticserach.media.article.settings.total_fields_limit'),
                'number_of_shards' => config('database.elasticserach.media.article.settings.number_of_shards'),
                'number_of_replicas' => config('database.elasticserach.media.article.settings.number_of_replicas'),
                'refresh_interval' => config('database.elasticserach.media.article.settings.refresh_interval')
            ]
        );
    }

    # index stats
    public function stats()
    {
        return Indices::stats([ 'articles', $this->id ]);
    }
}
