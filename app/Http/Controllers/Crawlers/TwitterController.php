<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\SetRequest;

use Carbon\Carbon;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

use App\Models\Log;
use App\Models\Twitter\StreamingKeywords;
use App\Models\Twitter\StreamingUsers;
use App\Models\Twitter\Account;
use App\Models\Option;

use App\Utilities\Term;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Jobs\Elasticsearch\CreateTwitterIndexJob;

class TwitterController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # dashboard
    # 
    public static function dashboard()
    {
        return view('crawlers.twitter.dashboard');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # index listesi view.
    # 
    public static function indices()
    {
        $rows = Option::whereIn('key', [
            'twitter.index.auto',
            'twitter.index.trends',
            'twitter.index.users'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        return view('crawlers.twitter.indices', compact('options'));
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

        $source = $client->get('/_cat/indices/olive__twitter*?format=json&s=index:desc')->getBody();
        $source = json_decode($source);

        return [
            'status' => 'ok',
            'hits' => $source
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # log ekranı json çıktısı.
    # 
    public static function logJson()
    {
        $date = Carbon::now()->subHours(24)->format('Y-m-d H:i:s');

        $logs = Log::where('module', 'ILIKE', '%twitter%')
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
                'twitter' => [
                    'tweets' => Indices::stats([ 'twitter', 'tweets', '*' ]),
                    'trends' => Indices::stats([ 'twitter', 'trends' ]),
                    'users' => Indices::stats([ 'twitter', 'users' ]),
                    'size' => Indices::stats([ 'twitter', '*' ])
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
    # trend başlıklar için index oluştur
    # 
    public static function indexCreate()
    {
        CreateTwitterIndexJob::dispatch('trends')->onQueue('elasticsearch');
        CreateTwitterIndexJob::dispatch('users')->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # twitter index durumu.
    # 
    public static function indexStatus()
    {
        return [
            'trends' => Indices::stats([ 'twitter', 'trends' ]),
            'users' => Indices::stats([ 'twitter', 'users' ])
        ];
    }
}
