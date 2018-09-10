<?php

namespace App\Models\Crawlers;

use Illuminate\Database\Eloquent\Model;
use App\Elasticsearch\Indices;

class SozlukCrawler extends Model
{
	protected $table = 'sozluk_crawlers';
    protected $fillable = [
        'name',
        'site',
        'url_pattern',
        'last_id',

        'selector_title',
        'selector_entry',
        'selector_author',

        'off_limit',
        'max_attempt',

        'status',
        'error_count'
    ];

    # index crate
    public function indexCreate()
    {
        return Indices::create(
            [
                'sozluk', $this->id
            ],
            [
                'entry' => [
                    'properties' => [
                        'id' => [
                            'type' => 'long'
                        ],
                        'group_name' => [
                            'type' => 'keyword'
                        ],
                        'bot_id' => [
                            'type' => 'integer'
                        ],
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'turkish',
                            'fielddata' => true
                        ],
                        'entry' => [
                            'type' => 'text',
                            'analyzer' => 'turkish',
                            'fielddata' => true
                        ],
                        'author' => [
                            'type' => 'text'
                        ],
                        'created_at' => [
                            'type' => 'date',
                            'format' => 'YYYY-MM-dd HH:mm:ss'
                        ],
                        'called_at' => [
                            'type' => 'date',
                            'format' => 'YYYY-MM-dd HH:mm:ss'
                        ],
                        'url' => [
                            'type' => 'text',
                            'index' => false
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
    }

    # index stats
    public function stats()
    {
        return Indices::stats([ 'sozluk', $this->id ]);
    }
}
