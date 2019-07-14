<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Elasticsearch\ClientBuilder;

use System;

class DataController extends Controller
{
    public function __construct()
    {
        /**
         ***** ZORUNLU *****
         *
         * - Kullanıcı
         */
        $this->middleware('auth');
    }

    /**
     * Veri Havuzu Ana Sayfa
     *
     * @return view
     */
    public static function dashboard()
    {
        return view('dataPool.dashboard');
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Elasticsearch Index kapatıp açma!
     *
     * @return array
     */
    public static function elasticsearchIndexStatus(Request $request)
    {
        $request->validate([
            'index_name' => 'required|string|max:255',
            'status' => 'required|string|in:close,open'
        ]);

        $client = ClientBuilder::fromConfig([
            'hosts' => config('database.connections.elasticsearch.node.ips'),
            'retries' => 5
        ]);

        try
        {
            $query = $client->indices()->{$request->status}([
                'index' => $request->index_name
            ]);

            System::log(
                auth()->user()->name.' index durumu değiştirdi.',
                'App\Http\Controllers\DataController::elasticsearchIndexStatus('.$request->index_name.', '.$request->status.')'
            );

            return [
                'status' => 'ok'
            ];
        }
        catch (\Exception $e)
        {
            $query = $e->getMessage();

            System::log(
                json_encode([
                    'Index durumu değiştirilemedi!',
                    $e->getMessage()
                ]),
                'App\Http\Controllers\DataController::elasticsearchIndexStatus('.$request->index_name.', '.$request->status.')'
            );

            return [
                'status' => 'err',
                'message' => $e->getMessage()
            ];
        }
    }
}
