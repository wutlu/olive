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
                                    // değiştirilmeyecek kelimeler.
                                    'turkish_keywords' => [
                                        'type' => 'keyword_marker',
                                        'keywords_path' => 'words/keywords.txt'
                                    ],
                                    // ilgilenilmeyecek kelimeler.
                                    'turkish_stop' => [
                                        'type' => 'stop',
                                        'stopwords_path' => 'words/stopwords.txt'
                                    ],
                                    'turkish_lowercase' => [
                                        'type' => 'lowercase',
                                        'language' => 'turkish'
                                    ],
                                    'turkish_stemmer' => [
                                        'type' => 'stemmer',
                                        'language' => 'turkish'
                                    ],
                                    // eş anlamlı kelimeler.
                                    'graph_synonyms' => [
                                        'type' => 'synonym_graph',
                                        'synonyms_path' => 'words/synonym.txt'
                                    ],
                                    // kelime yuvarlama.
                                    'my_snow' => [
                                        'type' => 'snowball',
                                        'language' => 'Turkish'
                                    ],
                                    'unique_stem' => [
                                      'type' => 'unique',
                                      'only_on_same_position' => true
                                    ],
                                    // girilen değerden büyük kelimelerle ilgileniyoruz.
                                    'my_script_filter' => [
                                        'type' => 'predicate_token_filter',
                                        'script' => [
                                            'source' => 'token.getTerm().length() > 5'  
                                        ]
                                    ]
                                ],

                                'analyzer' => [
                                    'turkish' => [
                                        'tokenizer' => 'standard',
                                        'filter' => [
                                            'classic', // kısaltmalardaki noktaları kaldırır.

                                            'turkish_lowercase',
                                            'keyword_repeat',
                                            'porter_stem',
                                            'unique_stem',

                                            'remove_duplicates', // aynı kelimeleri teke düşürür.
                                            'turkish_keywords', // keywords.txt içerisine girilen kelimeler üzerinde işlem yapmaz.
                                            'turkish_stop', // stopwords.txt dosyasındaki kelimelerle ilgilenilmeyecek.
                                            'apostrophe', // kesme işareti ve ayrılan ek'i saymaz.
                                            'turkish_stemmer', // sözcüğü köklerine ayırır. stopwords kelimeleri hariç.

                                            'fingerprint', // alan kazanma.
                                            'my_script_filter', // girilen değerden büyük kelimelerle ilgileniyoruz.
                                            'graph_synonyms', // eş anlamlı kelimeler.
                                            'my_snow',
                                            'min_hash', // hashleme yaparak alan kazanır.
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
