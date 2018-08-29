<?php

namespace App\Elasticsearch;

use Elasticsearch\ClientBuilder;

class Indices
{
    # elasticsearch index name
    public static function indexName(string $name)
    {
        return str_slug(config('app.name')).'__'.$name;
    }

    # elasticsearch index schema
    public static function indexCreate(array $name, array $mapping, array $params = [])
    {
        $name = self::indexName(implode('-', $name));

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
                                ]
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
                System::log($e->getMessage(), 'elasticsearch.index.create');

                return (object) [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
    }

    # elasticsearch index drop
    public static function indexDrop(string $name)
    {
        $name = self::indexName($name);

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
            System::log($e->getMessage(), 'elasticsearch.index.drop');

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    # elasticsearch index stat
    public static function indexStats(array $name)
    {
        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
            'retries' => 5
        ]);

        try
        {
            $es = $client->indices()->stats([
                'index' => self::indexName(implode('-', $name))
            ]);

            return (object) [
                'status' => 'ok',
                'data' => $es
            ];
        }
        catch (\Exception $e)
        {
            System::log($e->getMessage(), 'elasticsearch.index.stats');

            return (object) [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
