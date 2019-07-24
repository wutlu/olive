<?php

namespace App\Models\Crawlers\Instagram;

use Illuminate\Database\Eloquent\Model;

use App\Elasticsearch\Indices;

class Selves extends Model
{
    protected $table = 'instagram_selves';

    protected $fillable = [
        'hit',
        'control_interval'
    ];

    # organizasyon
    public function organisation()
    {
        return $this->hasOne('App\Models\Organisation\Organisation', 'id', 'organisation_id');
    }

    # index create
    public function indexCreate(string $type)
    {
        switch ($type)
        {
            case 'users':
                return Indices::create(
                    [
                        'instagram', 'users'
                    ],
                    [
                        'user' => [
                            'properties' => [
                                'id' => [ 'type' => 'long' ],
                                'name' => [ 'type' => 'keyword' ],
                                'screen_name' => [ 'type' => 'keyword' ],
                                'gender' => [ 'type' => 'keyword' ],
                                'image' => [
                                    'type' => 'keyword',
                                    'index' => false
                                ],
                                'external_url' => [
                                    'type' => 'keyword',
                                    'index' => false
                                ],
                                'description' => [
                                    'type' => 'text',
                                    'analyzer' => 'turkish',
                                    'fielddata' => true
                                ],
                                'verified' => [ 'type' => 'boolean' ],
                                'counts' => [
                                    'properties' => [
                                        'follow' => [ 'type' => 'integer' ],
                                        'followed_by' => [ 'type' => 'integer' ],
                                        'media' => [ 'type' => 'integer' ]
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
                                'deleted_at' => [
                                    'type' => 'date',
                                    'format' => 'YYYY-MM-dd HH:mm:ss'
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
                        'total_fields_limit' => config('database.elasticsearch.instagram.user.settings.total_fields_limit'),
                        'number_of_shards' => config('database.elasticsearch.instagram.user.settings.number_of_shards'),
                        'number_of_replicas' => config('database.elasticsearch.instagram.user.settings.number_of_replicas'),
                        'refresh_interval' => config('database.elasticsearch.instagram.user.settings.refresh_interval')
                    ]
                );
            break;

            default:
                return Indices::create(
                    [
                        'instagram', $type
                    ],
                    [
                    	'media' => [
                            'properties' => [
                                'id' => [ 'type' => 'long' ],
                                'self_id' => [ 'type' => 'integer' ],
                                'shortcode' => [ 'type' => 'keyword' ],
                                'display_url' => [
                                    'type' => 'keyword',
                                    'index' => false
                                ],
                                'user' => [
                                    'properties' => [
                                        'id' => [ 'type' => 'long' ],
                                        'name' => [ 'type' => 'keyword' ],
                                        'screen_name' => [ 'type' => 'keyword' ],
                                        'gender' => [ 'type' => 'keyword' ],
                                        'image' => [
                                            'type' => 'keyword',
                                            'index' => false
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
                                        'location' => [ 'type' => 'keyword' ],
                                        'lang' => [ 'type' => 'keyword' ],
                                        'verified' => [ 'type' => 'boolean' ],
                                        'protected' => [ 'type' => 'boolean' ]
                                    ]
                                ],
                                'counts' => [
                                    'properties' => [
                                        'hashtag' => [ 'type' => 'integer' ],
                                        'mention' => [ 'type' => 'integer' ]
                                    ]
                                ],
                                'type' => [ 'type' => 'keyword' ],
                                'place' => [
                                	'properties' => [
                                		'name' => [ 'type' => 'keyword' ]
                                	]
                                ],
                                'text' => [
                                    'type' => 'text',
                                    'analyzer' => 'turkish',
                                    'fielddata' => true
                                ],
                                'caption' => [
                                    'type' => 'text',
                                    'analyzer' => 'turkish',
                                    'fielddata' => true
                                ],
                                'entities' => [
                                    'properties' => [
                                        'hashtags' => [
                                            'type' => 'nested',
                                            'properties' => [
                                                'hashtag' => [ 'type' => 'keyword' ]
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
                                        ]
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
                        'total_fields_limit' => config('database.elasticsearch.instagram.media.settings.total_fields_limit'),
                        'number_of_shards' => config('database.elasticsearch.instagram.media.settings.number_of_shards'),
                        'number_of_replicas' => config('database.elasticsearch.instagram.media.settings.number_of_replicas'),
                        'refresh_interval' => config('database.elasticsearch.instagram.media.settings.refresh_interval')
                    ]
                );
            break;
        }
    }
}
