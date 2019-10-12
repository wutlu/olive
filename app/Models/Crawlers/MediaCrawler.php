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
        'state',

        'off_limit',
        'control_interval',
        'alexa_rank',

        'status',
        'error_count',
        'proxy',
        'count',
    ];

    # index create
    public function indexCreate(string $group)
    {
        return Indices::create(
            [
                'media', $group
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
                        'category' => [ 'type' => 'keyword' ],
                        'state' => [ 'type' => 'keyword' ],
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
                        'image_url' => [
                            'type' => 'keyword',
                            'index' => false
                        ],
                        'status' => [ 'type' => 'keyword' ],
                        'message' => [
                            'type' => 'text',
                            'index' => false
                        ],
                        'sentiment' => [
                            'properties' => [
                                'neg' => [ 'type' => 'float' ],
                                'pos' => [ 'type' => 'float' ],
                                'neu' => [ 'type' => 'float' ],
                                'hte' => [ 'type' => 'float' ],
                            ]
                        ],
                        'consumer' => [
                            'properties' => [
                                'que' => [ 'type' => 'float' ],
                                'req' => [ 'type' => 'float' ],
                                'cmp' => [ 'type' => 'float' ],
                                'nws' => [ 'type' => 'float' ],
                            ]
                        ],
                        'illegal' => [
                            'properties' => [
                                'bet' => [ 'type' => 'float' ],
                                'nud' => [ 'type' => 'float' ],
                                'nor' => [ 'type' => 'float' ],
                            ]
                        ]
                    ]
                ]
            ],
            [
                'total_fields_limit' => config('database.elasticsearch.media.article.settings.total_fields_limit'),
                'number_of_shards' => config('database.elasticsearch.media.article.settings.number_of_shards'),
                'number_of_replicas' => config('database.elasticsearch.media.article.settings.number_of_replicas'),
                'refresh_interval' => config('database.elasticsearch.media.article.settings.refresh_interval')
            ]
        );
    }

    # index stats
    public function stats()
    {
        return [];
    }
}
