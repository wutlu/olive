<?php

namespace App\Elasticsearch;

use Elasticsearch\ClientBuilder;
use System;

class Indices
{
    /**
     * Index Adı
     *
     * - Elasticsearch indexleri bir arada tutulur.
     * Bu durum çeşitli karışıklıklara neden olabileceğinden,
     * indexlere alias eklenerek bir önlem alınır.
     *
     * @return string
     */
    public static function name(array $name)
    {
        return str_slug(config('app.name')).'__'.implode('-', $name);
    }

    /**
     * Index Şeması
     *
     * @return object
     */
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
                                    'read_only_allow_delete' => false
                                ]
                            ],
                            'analysis' => [
                                'filter' => [
                                    # [ değiştirilmeyecek kelimeler ] #
                                    'turkish_keywords' => [
                                        'type' => 'keyword_marker',
                                        'keywords_path' => 'analysis/keywords.txt'
                                    ],
                                    # [ umursanmayacak kelimeler ] #
                                    'turkish_stop' => [
                                        'type' => 'stop',
                                        'stopwords_path' => 'analysis/stopwords.txt'
                                    ],
                                    'turkish_lowercase' => [
                                        'type' => 'lowercase',
                                        'language' => 'turkish'
                                    ],
                                    'turkish_stemmer' => [
                                        'type' => 'stemmer',
                                        'language' => 'turkish'
                                    ],
                                    # [ eş anlamlı kelimeler ] #
                                    /*
                                    'graph_synonyms' => [
                                        'type' => 'synonym_graph',
                                        'synonyms_path' => 'analysis/synonym.txt',
                                        'lenient' => true
                                    ],
                                    */
                                    # [ kelime yuvarlama ] #
                                    'my_snow' => [
                                        'type' => 'snowball',
                                        'language' => 'Turkish'
                                    ],
                                    # [ sadece 5 karakterden büyük kelimeler ] #
                                    /*
                                    'my_script_filter' => [
                                        'type' => 'predicate_token_filter',
                                        'script' => [
                                            'source' => 'token.getTerm().length() > 5'  
                                        ]
                                    ]
                                    */
                                ],
                                'analyzer' => [
                                    'turkish' => [
                                        'tokenizer' => 'standard',
                                        'filter' => [
                                            'classic', # [ bkz. gibi kısaltmalardaki . kaldırılır ] #
                                            'turkish_lowercase',
                                            'turkish_keywords',
                                            'turkish_stop',
                                            'apostrophe', # [ kesme işaretleri ve ayrılan ek kaldırılır ('de) gibi ] #
                                            'turkish_stemmer', # [ stopwords kelimeleri hariç tüm sözcükler eklerine ayrılır ] #
                                            //'my_script_filter',
                                            //'graph_synonyms',
                                            'my_snow'
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
                System::log(
                    json_encode(
                        $e->getMessage()
                    ),
                    'App\Elasticsearch\Indices::create('.$name.')',
                    10
                );

                return (object) [
                    'status' => 'err',
                    'message' => $e->getMessage()
                ];
            }
        }
    }

    /**
     * Index Sil
     *
     * @return object
     */
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
            System::log(
                json_encode(
                    $e->getMessage()
                ),
                'App\Elasticsearch\Indices::drop('.$name.')'
            );

            return [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Index Istatistikleri
     *
     * @return object
     */
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
            System::log(
                json_encode(
                    $e->getMessage()
                ),
                'App\Elasticsearch\Indices::stats('.$name.')'
            );

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Index Listesi
     *
     * @return object
     */
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

            return (object) [
                'status' => 'ok',
                'data' => $es
            ];
        }
        catch (\Exception $e)
        {
            System::log(
                json_encode(
                    $e->getMessage()
                ),
                'App\Elasticsearch\Indices::indices('.$name.')'
            );

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }
}

// curl -X PUT "192.168.44.1:9201/olive__twitter-tweets-2019.01/_mapping/tweet?pretty" -H 'Content-Type: application/json' -d'
// {
//   "properties": {
//     "user": {
//       "properties": {
//         "created_at": { 
//           "type": "date",
//           "format": "YYYY-MM-dd HH:mm:ss"
//         }
//       }
//     }
//   }
// }
// '
