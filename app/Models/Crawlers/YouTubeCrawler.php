<?php

namespace App\Models\Crawlers;

use App\Elasticsearch\Indices;

class YouTubeCrawler
{
    # index crate
    public function indexCreate(string $type)
    {
        switch ($type)
        {
            case 'video':
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
                                    'analyzer' => 'turkish'
                                ],
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
                                'counts' => [
                                    'properties' => [
                                        'view' => [ 'type' => 'long' ],
                                        'like' => [ 'type' => 'integer' ],
                                        'dislike' => [ 'type' => 'integer' ],
                                        'favorite' => [ 'type' => 'integer' ],
                                        'comment' => [ 'type' => 'integer' ]
                                    ]
                                ],
                                'channel' => [
                                    'properties' => [
                                        'id' => [ 'type' => 'keyword' ],
                                        'title' => [ 'type' => 'keyword' ]
                                    ]
                                ],
                                'sentiment' => [ 'type' => 'short' ]
                            ]
                        ]
                    ],
                    [
                        'total_fields_limit' => 44,
                        'number_of_shards' => 2,
                        'number_of_replicas' => 0,
                        'refresh_interval' => '10s'
                    ]
                );
            break;

            case 'comment':
                return Indices::create(
                    [
                        'youtube', 'comments'
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
                                'like' => [ 'type' => 'integer' ],
                                'dislike' => [ 'type' => 'integer' ],
                                'channel' => [
                                    'properties' => [
                                        'id' => [ 'type' => 'keyword' ],
                                        'title' => [ 'type' => 'keyword' ]
                                    ]
                                ],
                                'sentiment' => [ 'type' => 'short' ]
                            ]
                        ]
                    ],
                    [
                        'total_fields_limit' => 44,
                        'number_of_shards' => 4,
                        'number_of_replicas' => 1,
                        'refresh_interval' => '10s'
                    ]
                );
            break;
        }
    }
}
