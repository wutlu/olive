<?php

namespace App\Elasticsearch;

use Elasticsearch\ClientBuilder;

use System;

class Document
{
    # tek döküman
    public static function get($name, string $type, string $id)
    {
        if (is_array($name))
        {
            $name = Indices::name($name);
        }

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
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
            System::log(json_encode($e->getMessage()), 'App\Elasticsearch\Document::get('.$name.', '.$type.', '.$id.')');

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    # çok döküman
    public static function list(array $name, string $type, array $query)
    {
        $name = Indices::name($name);

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
            'retries' => 5
        ]);

        try
        {
            $docs = $client->search([
                'index' => $name,
                'type' => $type,
                'body' => $query
            ]);

            return (object) [
                'status' => 'ok',
                'data' => $docs
            ];
        }
        catch (\Exception $e)
        {
            System::log(json_encode($e->getMessage()), 'App\Elasticsearch\Document::list('.$name.', '.$type.', '.json_encode($query).')');

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    # elasticsearch bulk insert
    public static function bulkInsert(array $chunk)
    {
        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
            'retries' => 5
        ]);

        try
        {
            $query = (object) $client->bulk($chunk);

            if (@$query->status == 'error')
            {
                System::log(json_encode($query->message), 'App\Elasticsearch\Document::bulkInsert()', 5);

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
            System::log(json_encode([ $e->getMessage(), $chunk ]), 'App\Elasticsearch\Document::bulkInsert()', 10);

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    # elasticsearch döküman güncelle
    public static function patch(array $name, string $type, string $id, array $body = [])
    {
        $name = Indices::name($name);

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
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
            System::log(json_encode($e->getMessage()), 'App\Elasticsearch\Document::patch('.$name.', '.$type.')', 5);

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    # döküman sayısı
    public static function count(array $name, string $type, array $body = [])
    {
        $name = Indices::name($name);

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
            'retries' => 5
        ]);

        try
        {
            $doc = $client->count([
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
            System::log(json_encode($e->getMessage()), 'App\Elasticsearch\Document::count('.$name.', '.$type.', '.json_encode($body).')');

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    # döküman var mı?
    public static function exists($name, string $type, string $id)
    {
        if (is_array($name))
        {
            $name = Indices::name($name);
        }

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
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
            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }

    /*
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
     */
    # sorguyla döküman sil
    public static function deleteByQUery($name, string $type, array $body)
    {
        if (is_array($name))
        {
            $name = Indices::name($name);
        }

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.hosts'),
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
}
