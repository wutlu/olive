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
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list view
    # 
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # index listesi view.
    # 
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # index listesi json çıktısı.
    # 
    public static function indicesJson()
    {
        $client = new Client([
            'base_uri' => array_random(config('database.connections.elasticsearch.hosts')),
            'handler' => HandlerStack::create()
        ]);

        $source = $client->get('/_cat/indices/olive__youtube*?format=json&s=index:desc')->getBody();
        $source = json_decode($source);

        return [
            'status' => 'ok',
            'hits' => $source
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # log ekranı data
    # 
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # istatistikler
    # 
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # ayar güncelle
    # 
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin create index
    # 
    public static function indexCreate()
    {
        CreateYouTubeIndexJob::dispatch('videos')->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # youtube index durumu.
    # 
    public static function indexStatus()
    {
        return [
            'videos' => Indices::stats([ 'youtube', 'videos' ])
        ];
    }
}
