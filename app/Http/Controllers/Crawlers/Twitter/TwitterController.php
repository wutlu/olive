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

class TwitterController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter, token ve durum yönetimi ana sayfası.
     *
     * @return view
     */
    public static function dashboard()
    {
        $rows = Option::whereIn('key', [
            'twitter.status',
            'twitter.index.tweets'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        return view('crawlers.twitter.dashboard', compact('options'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter, Index listesi.
     *
     * @return view
     */
    public static function indices()
    {
        $rows = Option::whereIn('key', [
            'twitter.index.auto'
        ])->get();

        $options = [];

        foreach ($rows as $row)
        {
            $options[$row->key] = $row->value;
        }

        return view('crawlers.twitter.indices', compact('options'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter, Index listesi.
     *
     * @return array
     */
    public static function indicesJson()
    {
        $client = new Client([
            'base_uri' => array_random(config('database.connections.elasticsearch.hosts')),
            'handler' => HandlerStack::create()
        ]);

        $source = $client->get('/_cat/indices/'.config('system.db.alias').'__twitter*?format=json&s=index:desc')->getBody();
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
     * Twitter Logları
     *
     * @return array
     */
    public static function logJson()
    {
        $date = Carbon::now()->subHours(24)->format('Y-m-d H:i:s');

        $logs = Log::where('module', 'ILIKE', '%twitter%')->where('updated_at', '>', $date)->orderBy('updated_at', 'DESC')->get();

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
     * Twitter, Elasticsearch index istatistikleri.
     *
     * @return array
     */
    public static function statistics()
    {
        return json_encode(Indices::stats([ 'twitter', 'tweets', '*' ]));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Twitter, options tablosu ayar güncelleme.
     *
     * @return array
     */
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
}
