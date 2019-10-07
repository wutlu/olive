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

        'max_attempt',
        'deep_try',
        'chunk',

        'status',
        'pid',
        'proxy',

        'cookie'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'cookie' => 'array',
    ];

    public function getPidAttribute($pid)
    {
        $pid_term = posix_getpgid($pid);

        return $pid ? ($pid_term ? $pid : false) : null;
    }

    # index create
    public function indexCreate()
    {
        return Indices::create(
            [
                'sozluk', $this->id
            ],
            [
                'entry' => [
                    'properties' => [
                        'id' => [ 'type' => 'long' ],
                        'group_name' => [ 'type' => 'keyword' ],
                        'site_id' => [ 'type' => 'integer' ],
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
                        'category' => [ 'type' => 'keyword' ],
                        'author' => [ 'type' => 'keyword' ],
                        'gender' => [ 'type' => 'keyword' ],
                        'created_at' => [
                            'type' => 'date',
                            'format' => 'YYYY-MM-dd HH:mm:ss'
                        ],
                        'called_at' => [
                            'type' => 'date',
                            'format' => 'YYYY-MM-dd HH:mm:ss'
                        ],
                        'url' => [
                            'type' => 'keyword',
                            'index' => false
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
                'total_fields_limit' => config('database.elasticsearch.sozluk.entry.settings.total_fields_limit'),
                'number_of_shards' => config('database.elasticsearch.sozluk.entry.settings.number_of_shards'),
                'number_of_replicas' => config('database.elasticsearch.sozluk.entry.settings.number_of_replicas'),
                'refresh_interval' => config('database.elasticsearch.sozluk.entry.settings.refresh_interval')
            ]
        );
    }

    # index stats
    public function stats()
    {
        return Indices::stats([ 'sozluk', $this->id ]);
    }
}
