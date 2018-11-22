<?php

namespace App\Elasticsearch;

use Elasticsearch\ClientBuilder;
use System;

class Indices
{
    # elasticsearch index name
    public static function name(array $name)
    {
        return str_slug(config('app.name')).'__'.implode('-', $name);
    }

    # elasticsearch index schema
    public static function create(array $name, array $mapping, array $params = [])
    {
        $name = self::name($name);

        $default_params = [
            'total_fields_limit' => 5000,
            'number_of_shards' => 5,
            'number_of_replicas' => 1,
            'refresh_interval' => '1s'
        ];

        $params = (object) array_merge($default_params, $params);

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
            'retries' => 5
        ]);

        try
        {
            $indices = $client->indices()->exists([
                'index' => $name
            ]);
        }
        catch (\Exception $e)
        {
            $indices = false;
        }

        if ($indices)
        {
            return (object) [
                'status' => 'exists'
            ];
        }
        else
        {
            try
            {
                $response = $client->indices()->create([
                    'index' => $name,
                    'body' => [
                        'settings' => [
                            'mapping' => [
                                'total_fields' => [
                                    'limit' => $params->total_fields_limit
                                ],
                            ],
                            'number_of_shards' => $params->number_of_shards,
                            'number_of_replicas' => $params->number_of_replicas,
                            'refresh_interval' => $params->refresh_interval,
                            'index' => [
                                'blocks' => [
                                    'read_only_allow_delete' => null
                                ]
                            ],
                            'analysis' => [
                                'filter' => [
                                    'turkish_stop' => [
                                        'type' => 'stop',
                                        'stopwords' => '_turkish_' 
                                    ],
                                    'turkish_lowercase' => [
                                        'type' => 'lowercase',
                                        'language' => 'turkish'
                                    ],
                                    'turkish_stemmer' => [
                                        'type' => 'stemmer',
                                        'language' => 'turkish'
                                    ],
                                    'turkish_keywords' => [
                                        'type' => 'keyword_marker',
                                        'keywords' => explode(PHP_EOL, \File::get(database_path('words/stop.txt')))
                                    ]
                                ],

                                'analyzer' => [
                                    'turkish' => [
                                        'tokenizer' => 'standard',
                                        'filter' => [
                                            'apostrophe',
                                            'turkish_lowercase',
                                            'turkish_stop',
                                            'turkish_stemmer',
                                            'turkish_keywords'
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'mappings' => $mapping
                    ]
                ]);

                return (object) [
                    'status' => $response['acknowledged'] == true ? 'created' : 'failed'
                ];
            }
            catch (\Exception $e)
            {
                System::log(json_encode($e->getMessage()), 'App\Elasticsearch\Indices::create('.$name.')', 10);

                return (object) [
                    'status' => 'err',
                    'message' => $e->getMessage()
                ];
            }
        }
    }

    # elasticsearch index drop
    public static function drop(array $name)
    {
        $name = self::name($name);

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
            'retries' => 5
        ]);

        try
        {
            $es = $client->indices()->delete([
                'index' => $name
            ]);

            return [
                'status' => $es['acknowledged'] == true ? 'deleted' : 'failed'
            ];
        }
        catch (\Exception $e)
        {
            System::log(json_encode($e->getMessage()), 'App\Elasticsearch\Indices::drop('.$name.')');

            return [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    # elasticsearch index stat
    public static function stats(array $name)
    {
        $name = self::name($name);

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
            'retries' => 5
        ]);

        try
        {
            $es = $client->indices()->stats([
                'index' => $name
            ]);

            return (object) [
                'status' => 'ok',
                'data' => $es
            ];
        }
        catch (\Exception $e)
        {
            System::log(json_encode($e->getMessage()), 'App\Elasticsearch\Indices::stats('.$name.')');

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    # elasticsearch _cat/indices
    public static function indices(array $name = [])
    {
        $name = self::name($name);

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
            'retries' => 5
        ]);

        try
        {
            $es = $client->indices()->get();

            print_r($es);

            return (object) [
                'status' => 'ok',
                'data' => $es
            ];
        }
        catch (\Exception $e)
        {
            System::log(json_encode($e->getMessage()), 'App\Elasticsearch\Indices::indices('.$name.')');

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }
}
