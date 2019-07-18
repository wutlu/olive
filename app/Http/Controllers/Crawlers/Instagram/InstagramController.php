<?php

namespace App\Http\Controllers\Crawlers\Instagram;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\SetRequest;

use Carbon\Carbon;

use App\Elasticsearch\Indices;

use App\Models\Log;
use App\Models\Option;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Jobs\Elasticsearch\CreateInstagramIndexJob;

class InstagramController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Instagram, durum yönetimi ana sayfası.
     *
     * @return view
     */
    public static function dashboard()
    {
        $rows = Option::whereIn('key', [
            'instagram.status',
            'instagram.index.users',
            'instagram.index.medias'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        return view('crawlers.instagram.dashboard', compact('options'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Instagram, Index listesi.
     *
     * @return view
     */
    public static function indices()
    {
        $rows = Option::whereIn('key', [
            'instagram.index.users',
            'instagram.index.auto'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        return view('crawlers.instagram.indices', compact('options'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Instagram, Index listesi.
     *
     * @return array
     */
    public static function indicesJson()
    {
        $client = new Client([
            'base_uri' => array_random(config('database.connections.elasticsearch.node.ips')),
            'handler' => HandlerStack::create()
        ]);

        $source = $client->get('/_cat/indices/'.config('system.db.alias').'__instagram*?format=json&s=index:desc')->getBody();
        $source = json_decode($source);

        return [
            'status' => 'ok',
            'hits' => $source
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Instagram Logları
     *
     * @return array
     */
    public static function logJson()
    {
        $date = Carbon::now()->subHours(24)->format('Y-m-d H:i:s');

        $logs = Log::where('module', 'ILIKE', '%instagram%')->where('updated_at', '>', $date)->orderBy('updated_at', 'DESC')->get();

        return [
            'status' => 'ok',
            'data' => $logs
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Instagram, Elasticsearch index istatistikleri.
     *
     * @return array
     */
    public static function statistics()
    {
        return [
            'status' => 'ok',
            'data' => [
                'instagram' => [
                    'medias' => Indices::stats([ 'instagram', 'medias', '*' ]),
                    'users' => Indices::stats([ 'instagram', 'users' ])
                ]
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Instagram, options tablosu ayar güncelleme.
     *
     * @return array
     */
    public static function set(SetRequest $request)
    {
        $option = Option::where('key', $request->key)->first();

        $error = true;

        if (@$option)
        {
            if ($request->key == 'instagram.index.medias')
            {
                if ($option->value == date('Y.m', strtotime('+ 1 month')))
                {
                    $error = false;
                }
            }
            else
            {
                $error = false;
            }
        }

        if ($error)
        {
            return response(
                [
                    'status' => 'err',
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'token' => [ 'Önce index oluşturmanız gerekiyor.' ]
                    ]
                ],
                422
            );
        }

        Option::updateOrCreate(
            [
                'key' => $request->key
            ],
            [
                'value' => $request->value
            ]
        );

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Instagram kullanıcılar, Elasticsearch index oluşturma tetikleyicisi.
     *
     * @return array
     */
    public static function indexCreate()
    {
        CreateInstagramIndexJob::dispatch('users')->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Instagram Trend başlıklar, Elasticsearch index durumu.
     * - Index oluşturuldu mu, oluşturulmadı mı kontrolünü sağlar.
     *
     * @return array
     */
    public static function indexStatus()
    {
        return [
            'users' => Indices::stats([ 'instagram', 'users' ])
        ];
    }
}
