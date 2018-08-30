<?php

namespace App\Elasticsearch;

use Elasticsearch\ClientBuilder;
use System;

class Insert
{
    # elasticsearch bulk insert
    public static function bulk(array $chunk)
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
                System::log(json_encode($query->message), 'App\Elasticsearch\Insert::bulk()', 10);

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
            System::log(json_encode($e->getMessage()), 'App\Elasticsearch\Insert::bulk()', 10);

            return (object) [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }
}
