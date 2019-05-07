<?php

namespace App\Http\Controllers\Crawlers\YouTube;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\SetRequest;

use Carbon\Carbon;

use App\Elasticsearch\Indices;

use App\Models\Log;
use App\Models\Option;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Jobs\Elasticsearch\CreateYouTubeIndexJob;

class YouTubeController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube, durum yönetimi ana sayfası.
     *
     * @return view
     */
    public static function dashboard()
    {
        $rows = Option::whereIn('key', [
            'youtube.status',
            'youtube.index.videos',
            'youtube.index.comments'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        return view('crawlers.youtube.dashboard', compact('options'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube, index listesi.
     *
     * @return view
     */
    public static function indices()
    {
        $rows = Option::whereIn('key', [
            'youtube.index.auto',
            'youtube.index.videos'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        return view('crawlers.youtube.indices', compact('options'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube, index listesi.
     *
     * @return array
     */
    public static function indicesJson()
    {
        $client = new Client([
            'base_uri' => array_random(config('database.connections.elasticsearch.node.ips')),
            'handler' => HandlerStack::create()
        ]);

        $source = $client->get('/_cat/indices/'.config('system.db.alias').'__youtube*?format=json&s=index:desc')->getBody();
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
     * YouTube Logları
     *
     * @return array
     */
    public static function logJson()
    {
        $date = Carbon::now()->subHours(24)->format('Y-m-d H:i:s');

        $logs = Log::where('module', 'ILIKE', '%youtube%')
                   ->where('updated_at', '>', $date)
                   ->orderBy('updated_at', 'DESC')
                   ->get();

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
     * YouTube, Elasticsearch index istatistikleri.
     *
     * @return array
     */
    public static function statistics()
    {
        return [
            'status' => 'ok',
            'data' => [
                'youtube' => [
                    'comments' => Indices::stats([ 'youtube', 'comments', '*' ]),
                    'videos' => Indices::stats([ 'youtube', 'videos' ])
                ]
            ]
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube, options tablosu ayar güncelleme.
     *
     * @return array
     */
    public static function set(SetRequest $request)
    {
        $option = Option::where('key', $request->key)->first();

        $error = true;

        if (@$option)
        {
            if ($request->key == 'youtube.index.comments')
            {
                if ($option->value == date('Y.m', strtotime('+ 1 month')))
                {
                    $error = false;
                }
            }
            else if ($request->key == 'youtube.index.videos')
            {
                if ($option->value == 'on')
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
     * YouTube videolar, Elasticsearch index oluşturma tetikleyicisi.
     *
     * @return array
     */
    public static function indexCreate()
    {
        CreateYouTubeIndexJob::dispatch('videos')->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * YouTube Trend başlıklar, Elasticsearch index durumu.
     * - Index oluşturuldu mu, oluşturulmadı mı kontrolünü sağlar.
     *
     * @return array
     */
    public static function indexStatus()
    {
        return [
            'videos' => Indices::stats([ 'youtube', 'videos' ])
        ];
    }
}
