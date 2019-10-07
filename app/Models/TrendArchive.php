<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Elasticsearch\Indices;

class TrendArchive extends Model
{
    protected $table = 'trend_archives';
    protected $fillable = [
        'module',
        'group',
    ];

    public $disabled_mutator = false;

    public function getGroupAttribute($value)
    {
        if ($this->disabled_mutator)
        {
            return $value;
        }
        else
        {
            if (preg_match('/\d{4}\.\d{1,2}\.\d{1,2}-\d{1,2}/', $value))
            {
                $group = date('d.m.Y H:00', strtotime($this->created_at));
            }
            else if (preg_match('/\d{4}\.\d{1,2}\.\d{1,2}/', $value))
            {
                $group = date('d.m.Y', strtotime($this->created_at));
            }
            else if (preg_match('/\d{4}-\d{1,2}/', $value))
            {
                $group = str_replace('-', ', ', $value).'. hafta';
            }
            else if (preg_match('/\d{4}\.\d{1,2}/', $value))
            {
                $group = str_replace('.', ', ', $value).'. ay';
            }
            else
            {
                $group = date('d.m.Y H:i', strtotime($this->created_at)).' anlık';
            }

            return $group;
        }
    }

    # index crate
    public function indexCreate()
    {
        return Indices::create(
            [ 'trend', 'titles' ],
            [
                'title' => [
                    'properties' => [
                        'id' => [
                            'type' => 'keyword' // {module}-2018.12.31-23:59-{key}
                        ],
                        'group' => [
                            'type' => 'keyword' // 2018-52 | 2018.12 | 2018.12.31 | 2018.12.31-23 | 2018.12.31-23:59
                        ],
                        'module' => [
                            'type' => 'keyword' // twitter_tweet, twitter_favorite, twitter_hashtag, news, entry, youtube_video, google, blog, instagram
                        ],
                        'hit' => [
                            'type' => 'integer'
                        ],
                        'data' => [
                            'properties' => [
                                'id' => [ 'type' => 'keyword' ],
                                'title' => [
                                    'type' => 'text',
                                    'analyzer' => 'turkish'
                                ],
                                'text' => [
                                    'type' => 'text',
                                    'analyzer' => 'turkish'
                                ],
                                'key' => [ 'type' => 'keyword' ],
                                'image' => [
                                    'type' => 'keyword',
                                    'index' => false
                                ],
                                'user' => [
                                    'properties' => [
                                        'image' => [
                                            'type' => 'keyword',
                                            'index' => false
                                        ],
                                        'screen_name' => [ 'type' => 'keyword' ],
                                        'name' => [ 'type' => 'keyword' ],
                                        'id' => [ 'type' => 'long' ],
                                        'verified' => [ 'type' => 'boolean' ]
                                    ]
                                ],
                                'url' => [
                                    'type' => 'keyword',
                                    'index' => false
                                ],
                                'created_at' => [
                                    'type' => 'date',
                                    'format' => 'YYYY-MM-dd HH:mm:ss'
                                ]
                            ]
                        ],
                        'created_at' => [
                            'type' => 'date',
                            'format' => 'YYYY-MM-dd HH:mm:ss'
                        ]
                    ]
                ]
            ],
            [
                'total_fields_limit' => config('database.elasticsearch.trend.title.settings.total_fields_limit'),
                'number_of_shards' => config('database.elasticsearch.trend.title.settings.number_of_shards'),
                'number_of_replicas' => config('database.elasticsearch.trend.title.settings.number_of_replicas'),
                'refresh_interval' => config('database.elasticsearch.trend.title.settings.refresh_interval')
            ]
        );
    }
}
