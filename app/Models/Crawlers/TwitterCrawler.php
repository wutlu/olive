<?php

namespace App\Models\Crawlers;

use App\Elasticsearch\Indices;

class TwitterCrawler
{
    # index deseni
    public function indexCreate(string $type)
    {
        switch ($type)
        {
            case 'users':
                return Indices::create(
                    [ 'twitter', $type ],
                    [
                        'user' => [
                            'properties' => [
                                'id' => [ 'type' => 'long' ],
                                'created_at' => [
                                    'type' => 'date',
                                    'format' => 'YYYY-MM-dd HH:mm:ss'
                                ],
                                'name' => [
                                    'type' => 'text',
                                    'analyzer' => 'keyword',
                                    'fielddata' => true
                                ],
                                'screen_name' => [
                                    'type' => 'text',
                                    'analyzer' => 'keyword',
                                    'fielddata' => true
                                ],
                                'description' => [
                                    'type' => 'text',
                                    'analyzer' => 'turkish',
                                    'fielddata' => true
                                ],
                                'image' => [
                                    'type' => 'text',
                                    'index' => false
                                ],
                                'location' => [
                                    'type' => 'text',
                                    'analyzer' => 'keyword',
                                    'fielddata' => true
                                ],
                                'lang' => [
                                    'type' => 'text',
                                    'analyzer' => 'keyword',
                                    'fielddata' => true
                                ],
                                'verified' => [ 'type' => 'boolean' ],
                                'protected' => [ 'type' => 'boolean' ],
                                'counts' => [
                                    'properties' => [
                                        'statuses' => [ 'type' => 'integer' ],
                                        'favourites' => [ 'type' => 'integer' ],
                                        'listed' => [ 'type' => 'integer' ],
                                        'friends' => [ 'type' => 'integer' ],
                                        'followers' => [ 'type' => 'integer' ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'total_fields_limit' => 100,
                        'number_of_shards' => 1,
                        'number_of_replicas' => 1,
                        'refresh_interval' => '-1'
                    ]
                );
            break;

            case 'trends':
                return Indices::create(
                    [ 'twitter', $type ],
                    [
                        'trend' => [
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
                        'number_of_replicas' => 1,
                        'refresh_interval' => '10s'
                    ]
                );
            break;

            default:
                return Indices::create(
                    [ 'twitter', $type ],
                    [
                        'tweet' => [
                            'properties' => [
                                'id' => [ 'type' => 'long' ],
                                'text' => [
                                    'type' => 'text',
                                    'analyzer' => 'turkish',
                                    'fielddata' => true
                                ],
                                'sentiment' => [ 'type' => 'short' ],
                                'lang' => [
                                    'type' => 'text',
                                    'analyzer' => 'keyword',
                                    'fielddata' => true
                                ],
                                'platform' => [
                                    'type' => 'text',
                                    'analyzer' => 'keyword',
                                    'fielddata' => true
                                ],
                                'counts' => [
                                    'properties' => [
                                        'rt' => [ 'type' => 'integer' ],
                                        'fav' => [ 'type' => 'integer' ],
                                        'quote' => [ 'type' => 'integer' ],
                                        'reply' => [ 'type' => 'integer' ]
                                    ]
                                ],
                                'created_at' => [
                                    'type' => 'date',
                                    'format' => 'YYYY-MM-dd HH:mm:ss'
                                ],
                                'deleted_at' => [
                                    'type' => 'date',
                                    'format' => 'YYYY-MM-dd HH:mm:ss'
                                ],
                                'called_at' => [
                                    'type' => 'date',
                                    'format' => 'YYYY-MM-dd HH:mm:ss'
                                ],
                                'external' => [
                                    'properties' => [
                                        'id' => [ 'type' => 'long' ],
                                        'type' => [ 'type' => 'text' ]
                                    ]
                                ],
                                'place' => [
                                    'properties' => [
                                        'name' => [
                                            'type' => 'text',
                                            'analyzer' => 'keyword',
                                            'fielddata' => true
                                        ],
                                        'full_name' => [
                                            'type' => 'text',
                                            'analyzer' => 'keyword',
                                            'fielddata' => true
                                        ],
                                        'country_code' => [
                                            'type' => 'text',
                                            'analyzer' => 'keyword',
                                            'fielddata' => true
                                        ],
                                        'locations' => [
                                            'type' => 'nested',
                                            'properties' => [
                                                'coordinate' => [ 'type' => 'geo_point' ]
                                            ]
                                        ]
                                    ]
                                ],
                                'user' => [
                                    'properties' => [
                                        'id' => [ 'type' => 'long' ],
                                        'name' => [
                                            'type' => 'text',
                                            'analyzer' => 'keyword',
                                            'fielddata' => true
                                        ],
                                        'screen_name' => [
                                            'type' => 'text',
                                            'analyzer' => 'keyword',
                                            'fielddata' => true
                                        ]
                                    ]
                                ],
                                'entities' => [
                                    'properties' => [
                                        'hashtags' => [
                                            'type' => 'nested',
                                            'properties' => [
                                                'hashtag' => [
                                                    'type' => 'text',
                                                    'analyzer' => 'keyword',
                                                    'fielddata' => true
                                                ]
                                            ]
                                        ],
                                        'urls' => [
                                            'type' => 'nested',
                                            'properties' => [
                                                'url' => [
                                                    'type' => 'text',
                                                    'analyzer' => 'keyword',
                                                    'fielddata' => true
                                                ]
                                            ]
                                        ],
                                        'mentions' => [
                                            'type' => 'nested',
                                            'properties' => [
                                                'mention' => [
                                                    'properties' => [
                                                        'id' => [ 'type' => 'long' ],
                                                        'name' => [
                                                            'type' => 'text',
                                                            'analyzer' => 'keyword',
                                                            'fielddata' => true
                                                        ],
                                                        'screen_name' => [
                                                            'type' => 'text',
                                                            'analyzer' => 'keyword',
                                                            'fielddata' => true
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ],
                                        'medias' => [
                                            'type' => 'nested',
                                            'properties' => [
                                                'media' => [
                                                    'properties' => [
                                                        'id' => [ 'type' => 'long' ],
                                                        'media_url' => [
                                                            'type' => 'text',
                                                            'index' => false
                                                        ],
                                                        'source_url' => [
                                                            'type' => 'text',
                                                            'index' => false
                                                        ],
                                                        'type' => [
                                                            'type' => 'text',
                                                            'analyzer' => 'keyword',
                                                            'fielddata' => true
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'total_fields_limit' => 500,
                        'number_of_shards' => 4,
                        'number_of_replicas' => 1,
                        'refresh_interval' => '30s'
                    ]
                );
            break;
        }
    }
}
