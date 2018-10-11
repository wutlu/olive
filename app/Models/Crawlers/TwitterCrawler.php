<?php

namespace App\Models\Crawlers;

use App\Elasticsearch\Indices;

class TwitterCrawler
{
    # index crate
    public function indexCreate(string $type, string $version = '2006-03')
    {
        switch ($type)
        {
            case 'user':
                return Indices::create(
                    [ 'twitter', 'users' ],
                    [
                        'user' => [
                            'properties' => [
                                'id' => [
                                    'type' => 'long'
                                ],
                                //----
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

            case 'tweet':
                return Indices::create(
                    [ 'twitter', 'tweets', $version ],
                    [
                        'tweet' => [
                            'properties' => [
                                'id' => [
                                    'type' => 'long'
                                ],
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
