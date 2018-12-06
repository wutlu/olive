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
    ];

    public function getPidAttribute($pid)
    {
        $pid_term = posix_getpgid($pid);

        return $pid ? ($pid_term ? $pid : false) : null;
    }

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
                        ],
                        'sentiment' => [
                            'properties' => [
                                'neg' => [ 'type' => 'float' ],
                                'pos' => [ 'type' => 'float' ],
                                'neu' => [ 'type' => 'float' ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'total_fields_limit' => config('database.elasticserach.sozluk.entry.settings.total_fields_limit'),
                'number_of_shards' => config('database.elasticserach.sozluk.entry.settings.number_of_shards'),
                'number_of_replicas' => config('database.elasticserach.sozluk.entry.settings.number_of_replicas'),
                'refresh_interval' => config('database.elasticserach.sozluk.entry.settings.refresh_interval')
            ]
        );
    }

    # index stats
    public function stats()
    {
        return Indices::stats([ 'sozluk', $this->id ]);
    }
}
