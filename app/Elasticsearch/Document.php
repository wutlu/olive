<?php

namespace App\Elasticsearch;

use Elasticsearch\ClientBuilder;

use System;

class Document
{
    /**
     * Tek Döküman Çağır
     *
     * @return object
     */
    public static function get($name, string $type, string $id)
    {
        if (is_array($name))
        {
            $name = Indices::name($name);
        }

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.node.ips'),
            'retries' => 5
        ]);

        try
        {
            $doc = $client->get([
                'index' => $name,
                'type' => $type,
                'id' => $id
            ]);

            return (object) [
                'status' => 'ok',
                'data' => $doc
            ];
        }
        catch (\Exception $e)
        {
            System::log(
                $e->getMessage(),
                'App\Elasticsearch\Document::get('.$name.', '.$type.', '.$id.')'
            );

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Döküman Listele
     *
     * @return object
     */
    public static function search($name, string $type, array $query)
    {
        if (is_array($name))
        {
            $name = Indices::name($name);
        }

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.node.ips'),
            'retries' => 5
        ]);

        try
        {
            $arr = [
                'index' => $name,
                'type' => $type,
                'body' => $query
            ];

            $docs = $client->search($arr);

            return (object) [
                'status' => 'ok',
                'data' => $docs
            ];
        }
        catch (\Exception $e)
        {
            System::log(
                $e->getMessage(),
                'App\Elasticsearch\Document::search('.$name.', '.$type.', '.json_encode($query).')'
            );

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Toplu İş Girişi
     *
     * @return object
     */
    public static function bulkInsert(array $chunk)
    {
        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.node.ips'),
            'retries' => 5
        ]);

        try
        {
            $query = (object) $client->bulk($chunk);

            if (@$query->status == 'error')
            {
                System::log(
                    json_encode(
                        $query->message
                    ),
                    'App\Elasticsearch\Document::bulkInsert()',
                    5
                );

                return (object) [
                    'status' => 'err',
                    'message' => $query->message
                ];
            }
            else
            {
                return (object) [
                    'status' => 'ok',
                    'result' => $query
                ];
            }
        }
        catch (\Exception $e)
        {
            System::log(
                json_encode(
                    [
                        $e->getMessage(),
                        $chunk
                    ]
                ),
                'App\Elasticsearch\Document::bulkInsert()',
                9
            );

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Döküman Güncelle
     *
     * @return object
     */
    public static function patch(array $name, string $type, string $id, array $body = [])
    {
        $name = Indices::name($name);

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.node.ips'),
            'retries' => 5
        ]);

        try
        {
            $es = $client->update([
                'index' => $name,
                'type' => $type,
                'id' => $id,
                'body' => $body
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
                    [
                        $e->getMessage(),
                        $body
                    ]
                ),
                'App\Elasticsearch\Document::patch('.$name.'/'.$type.'/'.$id.')',
                5
            );

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Döküman Sayısı
     *
     * @return object
     */
    public static function count($name, string $type = '', array $body = [])
    {
        if (is_array($name))
        {
            $name = Indices::name($name);
        }

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.node.ips'),
            'retries' => 5
        ]);

        try
        {
            $arr = [
                'index' => $name
            ];

            if ($type)
            {
                $arr['type'] = $type;
            }

            if ($body)
            {
                $arr['body'] = $body;
            }

            $doc = $client->count($arr);

            return (object) [
                'status' => 'ok',
                'data' => $doc
            ];
        }
        catch (\Exception $e)
        {
            System::log(
                $e->getMessage(),
                'App\Elasticsearch\Document::count('.$name.', '.$type.', '.json_encode($body).')'
            );

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Döküman Kontrolü
     *
     * @return object
     */
    public static function exists($name, string $type, string $id)
    {
        if (is_array($name))
        {
            $name = Indices::name($name);
        }

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.node.ips'),
            'retries' => 5
        ]);

        try
        {
            $doc = $client->get([
                'index' => $name,
                'type' => $type,
                'id' => $id
            ]);

            return (object) [
                'status' => 'ok',
                'data' => $doc
            ];
        }
        catch (\Exception $e)
        {
            System::log(
                $e->getMessage(),
                'App\Elasticsearch\Document::exists('.$name.', '.$type.', '.$id.')'
            );

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Döküman Sil
     *
     *   curl -X POST "localhost:9201/ * /_delete_by_query?pretty" -H 'Content-Type: application/json' -d'
     *   {
     *       "query": { 
     *           "bool": {
     *               "filter": {
     *                   "range": {
     *                       "called_at": { "format": "YYYY-MM-dd", "gte": "2019-05-07" }
     *                   }
     *               }
     *           }
     *       }
     *   }
     *   '
     *
     *   curl -X POST "192.168.44.1:9200/ * /_delete_by_query?pretty" -H 'Content-Type: application/json' -d'
     *   {
     *       "query": { 
     *           "bool": {
     *               "filter": {
     *                   "range": {
     *                       "created_at": { "format": "YYYY-MM-dd", "gte": "2018-12-10" }
     *                   }
     *               }
     *           }
     *       }
     *   }
     *   '

     * curl -X POST "localhost:9201/olive__media-s/_delete_by_query" -H 'Content-Type: application/json' -d'
     * {
     *     "query": { 
     *         "bool": {
     *             "must": [
     *                 {
     *                     "exists": {
     *                         "field: "sentiment.sentiment-pos"
     *                     }
     *                 }
     *             ]
     *         }
     *     }
     * }
     * '

     * curl -X POST "localhost:9201/oliveone__media-s14/_delete_by_query?pretty" -H 'Content-Type: application/json' -d'
     * {
     *     "query": { 
     *         "bool": {
     *             "must": [
     *                 { "match": { "site_id": 273 } },
     *                 {
     *                     "query_string": {
     *                         "query": "Benzer haberler"
     *                     }
     *                 }
     *             ]
     *         }
     *     }
     * }
     * '
     *
     * @return object
     */
    public static function deleteByQuery($name, string $type, array $body)
    {
        if (is_array($name))
        {
            $name = Indices::name($name);
        }

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.node.ips'),
            'retries' => 5
        ]);

        try
        {
            $doc = $client->deleteByQuery([
                'index' => $name,
                'type' => $type,
                'body' => $body
            ]);

            return (object) [
                'status' => 'ok',
                'data' => $doc
            ];
        }
        catch (\Exception $e)
        {
            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Tekil Döküman Sİl
     *
     * @return array
     *
     */
    public static function delete($name, string $type, string $id)
    {
        if (is_array($name))
        {
            $name = Indices::name($name);
        }

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.node.ips'),
            'retries' => 5
        ]);

        try
        {
            $doc = $client->delete([
                'index' => $name,
                'type' => $type,
                'id' => $id
            ]);

            return (object) [
                'status' => 'ok',
                'data' => $doc
            ];
        }
        catch (\Exception $e)
        {
            System::log(
                json_encode(
                    $e->getMessage()
                ),
                'App\Elasticsearch\Document::delete('.$name.', '.$type.', '.$id.')'
            );

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }
}
