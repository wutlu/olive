<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Elasticsearch\Indices;

class Trend extends Model
{
    protected $table = 'trends';
    protected $fillable = [
        'title',
        'group'
    ];

    # index crate
    public function indexCreate()
    {
        return Indices::create(
            [ 'trend', 'titles' ],
            [
                'title' => [
                    'properties' => [
                        'id' => [
                            'type' => 'keyword' // {module}_{key}_2018.12.31.23.00
                        ],
                        'group' => [
                            'type' => 'keyword' // yearly_2018 | monthly_2018.12 | weekly_2018.52 | daily_2018.12.31 | hourly_2018.12.31.23 | minutely_2018.12.31.23.59
                        ],
                        'module' => [
                            'type' => 'keyword' // youtube_video, youtube_comment, twitter, sozluk, news, shopping
                        ],
                        'key' => [
                            'type' => 'keyword'
                        ],
                        'rank' => [
                            'type' => 'integer'
                        ],
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'keyword',
                            'fielddata' => true
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
