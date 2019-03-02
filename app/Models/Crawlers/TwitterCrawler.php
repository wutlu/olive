<?php

namespace App\Models\Crawlers;

use App\Elasticsearch\Indices;

use App\Jobs\Elasticsearch\BulkInsertJob;

class TwitterCrawler
{
    # chunk
    public static function chunk($obj, array $bulk = [])
    {
        $bulk['body'][] = [
            'create' => [
                '_index' => Indices::name([ 'twitter', 'tweets', date('Y.m', strtotime($obj->created_at)) ]),
                '_type' => 'tweet',
                '_id' => $obj->id
            ]
        ];
        $bulk['body'][] = $obj;

        $total = count($bulk['body'])/2;

        if ($total >= config('services.twitter.chunk_count'))
        {
            BulkInsertJob::dispatch($bulk)->onQueue('elasticsearch');

            unset($bulk);

            $bulk = [];
        }

        return $bulk;
    }

    # obje deseni
    public static function pattern(array $object)
    {
        $arr = [
            'id' => $object['id_str'],
            'text' => @$object['extended_tweet']['full_text'] ? $object['extended_tweet']['full_text'] : $object['text'],
            'platform' => strip_tags($object['source']),
            'lang' => $object['lang'],
            'created_at' => date('Y-m-d H:i:s', strtotime($object['created_at'])),
            'called_at' => date('Y-m-d H:i:s'),
            'user' => (object) [
                'id' => $object['user']['id_str'],
                'screen_name' => $object['user']['screen_name'],
                'name' => $object['user']['name'],
                'image' => $object['user']['profile_image_url'],
                'lang' => $object['user']['lang'],
                'created_at' => date('Y-m-d H:i:s', strtotime($object['user']['created_at'])),
                'counts' => [
                    'statuses' => intval($object['user']['statuses_count']),
                    'favourites' => intval($object['user']['favourites_count']),
                    'listed' => intval($object['user']['listed_count']),
                    'friends' => intval($object['user']['friends_count']),
                    'followers' => intval($object['user']['followers_count'])
                ]
            ]
        ];

        if ($object['user']['description'])
        {
            $arr['user']->description = $object['user']['description'];
        }

        if ($object['user']['location'])
        {
            $arr['user']->location = $object['user']['location'];
        }

        if ($object['user']['verified'])
        {
            $arr['user']->verified = true;
        }

        if ($object['user']['protected'])
        {
            $arr['user']->protected = true;
        }

        # 
        # tweet satatus
        # 
        if (@$object['retweeted_status']['id_str'])
        {
            $arr['external'] = (object) [
                'id' => $object['retweeted_status']['id_str'],
                'type' => 'retweet'
            ];
        }
        elseif (@$object['in_reply_to_status_id_str'])
        {
            $arr['external'] = (object) [
                'id' => $object['in_reply_to_status_id_str'],
                'type' => 'reply'
            ];
        }
        elseif (@$object['quoted_status']['id_str'])
        {
            $arr['external'] = (object) [
                'id' => $object['quoted_status']['id_str'],
                'type' => 'quote'
            ];
        }

        # 
        # tweet place
        # 
        if (@$object['place'])
        {
            $arr['place'] = (object) [
                'name' => $object['place']['name'],
                'full_name' => $object['place']['full_name'],
                'country_code' => $object['place']['country_code']
            ];
        }

        # 
        # hashtags
        # 
        if (@$object['entities']['hashtags'])
        {
            $arr['entities']['hashtags'] = array_map(function($obj) {
                return (object) [ 'hashtag'  => $obj['text'] ];
            }, $object['entities']['hashtags']);
        }

        # 
        # urls
        # 
        if (@$object['entities']['urls'])
        {
            $arr['entities']['urls'] = array_map(function($obj) {
                return (object) [ 'url'  => $obj['expanded_url'] ];
            }, $object['entities']['urls']);
        }

        # 
        # mentions
        # 
        if (@$object['entities']['user_mentions'])
        {
            $arr['entities']['mentions'] = array_map(function($obj) {
                return (object) [
                    'mention' => [
                        'id'  => $obj['id_str'],
                        'name'  => $obj['name'],
                        'screen_name'  => $obj['screen_name']
                    ]
                ];
            }, $object['entities']['user_mentions']);
        }

        # 
        # medias
        # 
        if (@$object['extended_entities']['media'])
        {
            $arr['entities']['medias'] = array_map(function($obj) {
                $media_arr = [
                    'media_url'  => $obj['media_url'],
                    'type'  => $obj['type']
                ];

                if (@$obj['video_info']['variants'])
                {
                    foreach ($obj['video_info']['variants'] as $v)
                    {
                        if ($v['content_type'] == 'video/mp4')
                        {
                            $media_arr['source_url'] = $v['url'];
                        }
                    }
                }

                return (object) [ 'media' => $media_arr ];
            }, $object['extended_entities']['media']);
        }

        return $arr;
    }

    # index deseni
    public function indexCreate(string $type)
    {
        return Indices::create(
            [
                'twitter',
                $type
            ],
            [
                'tweet' => [
                    'properties' => [
                        'id' => [ 'type' => 'long' ],
                        'text' => [
                            'type' => 'text',
                            'analyzer' => 'turkish',
                            'fielddata' => true
                        ],
                        'sentiment' => [
                            'properties' => [
                                'neg' => [ 'type' => 'float' ],
                                'pos' => [ 'type' => 'float' ],
                                'neu' => [ 'type' => 'float' ]
                            ]
                        ],
                        'lang' => [ 'type' => 'keyword' ],
                        'platform' => [ 'type' => 'keyword' ],
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
                                'type' => [ 'type' => 'keyword' ]
                            ]
                        ],
                        'place' => [
                            'properties' => [
                                'name' => [ 'type' => 'keyword' ],
                                'full_name' => [ 'type' => 'keyword' ],
                                'country_code' => [ 'type' => 'keyword' ]
                            ]
                        ],
                        'user' => [
                            'properties' => [
                                'id' => [ 'type' => 'long' ],
                                'name' => [ 'type' => 'keyword' ],
                                'screen_name' => [ 'type' => 'keyword' ],
                                'image' => [ 'type' => 'keyword' ],
                                'description' => [
                                    'type' => 'text',
                                    'analyzer' => 'turkish',
                                    'fielddata' => true
                                ],
                                'created_at' => [
                                    'type' => 'date',
                                    'format' => 'YYYY-MM-dd HH:mm:ss'
                                ],
                                'location' => [ 'type' => 'keyword' ],
                                'lang' => [ 'type' => 'keyword' ],
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
                        ],
                        'entities' => [
                            'properties' => [
                                'hashtags' => [
                                    'type' => 'nested',
                                    'properties' => [
                                        'hashtag' => [ 'type' => 'keyword' ]
                                    ]
                                ],
                                'urls' => [
                                    'type' => 'nested',
                                    'properties' => [
                                        'url' => [ 'type' => 'keyword' ]
                                    ]
                                ],
                                'mentions' => [
                                    'type' => 'nested',
                                    'properties' => [
                                        'mention' => [
                                            'properties' => [
                                                'id' => [ 'type' => 'long' ],
                                                'name' => [ 'type' => 'keyword' ],
                                                'screen_name' => [ 'type' => 'keyword' ]
                                            ]
                                        ]
                                    ]
                                ],
                                'medias' => [
                                    'type' => 'nested',
                                    'properties' => [
                                        'media' => [
                                            'properties' => [
                                                'media_url' => [
                                                    'type' => 'keyword'
                                                ],
                                                'source_url' => [
                                                    'type' => 'keyword'
                                                ],
                                                'type' => [ 'type' => 'keyword' ]
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
                'total_fields_limit' => config('database.elasticsearch.twitter.tweet.settings.total_fields_limit'),
                'number_of_shards' => config('database.elasticsearch.twitter.tweet.settings.number_of_shards'),
                'number_of_replicas' => config('database.elasticsearch.twitter.tweet.settings.number_of_replicas'),
                'refresh_interval' => config('database.elasticsearch.twitter.tweet.settings.refresh_interval')
            ]
        );
    }
}
