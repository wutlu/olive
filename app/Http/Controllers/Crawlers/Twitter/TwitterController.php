<?php

namespace App\Http\Controllers\Crawlers\Twitter;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\SetRequest;

use Carbon\Carbon;

use App\Elasticsearch\Indices;

use App\Models\Log;
use App\Models\Option;
use App\Models\Twitter\Account;

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
        $rows = Option::whereIn('key', [
            'twitter.trend.status',
            'twitter.status',
            'twitter.index.trends',
            'twitter.index.tweets'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        return view('crawlers.twitter.dashboard', compact('options'));
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # index listesi view.
    # 
    public static function indices()
    {
        $rows = Option::whereIn('key', [
            'twitter.index.auto',
            'twitter.index.trends'
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
                    'trends' => Indices::stats([ 'twitter', 'trends' ])
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
            if ($request->key == 'twitter.index.tweets')
            {
                if ($option->value == date('Y.m', strtotime('+ 1 month')))
                {
                    $error = false;
                }
            }
            else if ($request->key == 'twitter.index.trends')
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
    # trend başlıklar için index oluştur.
    # 
    public static function indexCreate()
    {
        CreateTwitterIndexJob::dispatch('trends')->onQueue('elasticsearch');

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
            'trends' => Indices::stats([ 'twitter', 'trends' ])
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # bağlı hesaplar view
    # 
    public static function accounts()
    {
        return view('crawlers.twitter.accounts');
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # bağlı hesaplar json
    # 
    public static function accountsViewJson(SearchRequest $request)
    {
        $take = $request->take;
        $skip = $request->skip;

        $query = new Account;
        $query = $request->string ? $query->orWhere('name', 'ILIKE', '%'.$request->string.'%')
                                          ->orWhere('screen_name', 'ILIKE', '%'.$request->string.'%') : $query;
        $query = $query->skip($skip)
                       ->take($take)
                       ->orderBy('updated_at', 'DESC');

        return [
            'status' => 'ok',
            'hits' => $query->get(),
            'total' => $query->count()
        ];
    }
}
