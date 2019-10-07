<?php

namespace App\Models\Crawlers;

use App\Elasticsearch\Indices;

class YouTubeCrawler
{
    # index create
    public function indexCreate(string $type)
    {
        switch ($type)
        {
            case 'videos':
                return Indices::create(
                    [
                        'youtube', 'videos'
                    ],
                    [
                        'video' => [
                            'properties' => [
                                'id' => [ 'type' => 'keyword' ],
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
                                'tags' => [
                                    'type' => 'nested',
                                    'properties' => [
                                        'tag' => [ 'type' => 'keyword' ]
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
                                'channel' => [
                                    'properties' => [
                                        'id' => [ 'type' => 'keyword' ],
                                        'title' => [ 'type' => 'keyword' ],
                                        'gender' => [ 'type' => 'keyword' ]
                                    ]
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
                        'total_fields_limit' => config('database.elasticsearch.youtube.video.settings.total_fields_limit'),
                        'number_of_shards' => config('database.elasticsearch.youtube.video.settings.number_of_shards'),
                        'number_of_replicas' => config('database.elasticsearch.youtube.video.settings.number_of_replicas'),
                        'refresh_interval' => config('database.elasticsearch.youtube.video.settings.refresh_interval')
                    ]
                );
            break;

            default:
                return Indices::create(
                    [
                        'youtube', $type
                    ],
                    [
                        'comment' => [
                            'properties' => [
                                'id' => [ 'type' => 'keyword' ],
                                'text' => [
                                    'type' => 'text',
                                    'analyzer' => 'turkish',
                                    'fielddata' => true
                                ],
                                'category' => [ 'type' => 'keyword' ],
                                'video_id' => [ 'type' => 'keyword' ],
                                'comment_id' => [ 'type' => 'keyword' ],
                                'created_at' => [
                                    'type' => 'date',
                                    'format' => 'YYYY-MM-dd HH:mm:ss'
                                ],
                                'called_at' => [
                                    'type' => 'date',
                                    'format' => 'YYYY-MM-dd HH:mm:ss'
                                ],
                                'channel' => [
                                    'properties' => [
                                        'id' => [ 'type' => 'keyword' ],
                                        'title' => [ 'type' => 'keyword' ],
                                        'gender' => [ 'type' => 'keyword' ]
                                    ]
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
                        'total_fields_limit' => config('database.elasticsearch.youtube.comment.settings.total_fields_limit'),
                        'number_of_shards' => config('database.elasticsearch.youtube.comment.settings.number_of_shards'),
                        'number_of_replicas' => config('database.elasticsearch.youtube.comment.settings.number_of_replicas'),
                        'refresh_interval' => config('database.elasticsearch.youtube.comment.settings.refresh_interval')
                    ]
                );
            break;
        }
    }
}
