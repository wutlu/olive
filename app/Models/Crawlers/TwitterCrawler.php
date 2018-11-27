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
            'sentiment' => 0,
            'lang' => $object['lang'],
            'counts' => (object) [
                'rt' => intval($object['retweet_count']),
                'fav' => intval($object['favorite_count']),
                'quote' => intval($object['quote_count']),
                'reply' => intval($object['reply_count'])
            ],
            'created_at' => date('Y-m-d H:i:s', strtotime($object['created_at'])),
            'called_at' => date('Y-m-d H:i:s'),
            'user' => (object) [
                'id' => $object['user']['id_str'],
                'screen_name' => $object['user']['screen_name'],
                'name' => $object['user']['name'],
                'image' => $object['user']['profile_image_url'],
                'lang' => $object['user']['lang'],
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
                    'id'  => $obj['id_str'],
                    'name'  => $obj['name'],
                    'screen_name'  => $obj['screen_name']
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

                return (object) $media_arr;
            }, $object['extended_entities']['media']);
        }

        return (object) $arr;
    }

    # index deseni
    public function indexCreate(string $type)
    {
        switch ($type)
        {
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
                                    'analyzer' => 'keyword',
                                    'fielddata' => true
                                ],
                                'approx_traffic' => [
                                    'type' => 'integer'
                                ],
                                'created_at' => [
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
                                'sentiment' => [
                                    'properties' => [
                                        'neg' => [ 'type' => 'float' ],
                                        'pos' => [ 'type' => 'float' ],
                                        'neu' => [ 'type' => 'float' ]
                                    ]
                                ],
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
                        'number_of_shards' => 2,
                        'number_of_replicas' => 1,
                        'refresh_interval' => '30s'
                    ]
                );
            break;
        }
    }
}
