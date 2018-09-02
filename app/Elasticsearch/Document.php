<?php

namespace App\Elasticsearch;

use Elasticsearch\ClientBuilder;

use System;

class Document
{
    # Tek döküman.
    public static function get(array $name, string $type, string $id)
    {
        $name = Indices::name($name);

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

    # Çok döküman
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
                System::log(json_encode($query->message), 'App\Elasticsearch\Document::bulkInsert()', 10);

                return (object) [
                    'status' => 'err',
                    'message' => $query->message
                ];
            }
            else
            {
                return (object) [
                    'status' => 'ok'
                ];
            }
        }
        catch (\Exception $e)
        {
            System::log(json_encode($e->getMessage()), 'App\Elasticsearch\Document::bulkInsert()', 10);

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
            System::log(json_encode($e->getMessage()), 'App\Elasticsearch\Document::patch('.$name.', '.$type.')', 10);

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }
}
