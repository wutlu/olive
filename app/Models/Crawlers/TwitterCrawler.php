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
                                'id' => [
                                    'type' => 'long'
                                ]
                            ]
                        ]
                    ],
                    [
                        'total_fields_limit' => 100,
                        'number_of_shards' => 4,
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
                                'id' => [
                                    'type' => 'long'
                                ],
            /*
                                body
                                sentiment
                                lang
                                platform
                                counts
                                    rt
                                    fav
                                    quote
                                    reply
                                created_at
                                deleted_at
                                called_at
                                external
                                    id
                                    type
                                place
                                    id
                                    type
                                    name
                                    full_name
                                    country
                                    country_code
                                coordinates
                                entities
                                    hashtags
                                        hashtags
                                    urls
                                        url
                                    mentions
                                        id
                                        screen_name
                                        name
                                    medias
                                        id
                                        media_url
                                        source_url
                                        type
                                user
                                    id
                                    screen_name
                                    name
                                    description
                                    url
                                    location
                                    lang
                                    verified
                                    protected
                                    time_zone
                                    counts
                                        statuses
                                        favourites
                                        listed
                                        friends
                                        fllowers
                */
                            ]
                        ]
                    ],
                    [
                        'total_fields_limit' => 500,
                        'number_of_shards' => 6,
                        'number_of_replicas' => 1,
                        'refresh_interval' => '30s'
                    ]
                );
            break;
        }
    }
}
