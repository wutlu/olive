<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\SetRequest;

use App\Jobs\Elasticsearch\CreateTrendIndexJob;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

use App\Models\Option;
use App\Models\Log;

use Carbon\Carbon;

use System;

class TrendController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Trend, durum yönetimi ana sayfası.
     *
     * @return view
     */
    public static function dashboard()
    {
    	$rows = Option::whereIn('key', array_merge(array_keys(config('system.trends')), [ 'trend.index' ]))->get();

    	$options = [];

    	foreach ($rows as $row)
    	{
    		$options[$row->key] = $row->value;
    	}

        return view('trends.dashboard', compact('options'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Trend, Elasticsearch index oluşturma tetikleyicisi.
     *
     * @return array
     */
    public static function indexCreate()
    {
    	System::log('Trend indexi oluşturma isteği gönderildi.', 'App\Http\Controllers\TrendController::indexCreate()', 1);

        CreateTrendIndexJob::dispatch()->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Trend, Elasticsearch index durumu.
     * - Index oluşturuldu mu, oluşturulmadı mı kontrolünü sağlar.
     *
     * @return array
     */
    public static function indexStatus()
    {
        $count = Option::whereIn('key', [
            'trend.index'
        ])->where('value', 'on')->count();

        $data = [];
        $data_keys = Option::whereIn('key', array_keys(config('system.trends')))->get();

        if (count($data_keys))
        {
        	foreach ($data_keys as $d)
        	{
        		$key = explode('.', $d->key);

        		$data[end($key)] = $d->value == 'on' ? Document::count([ 'trend', 'titles' ], 'title', [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [ 'match' => [ 'module' => end($key) ] ]
                            ]
                        ]
                    ]
                ]) : 'off';
        	}
        }

    	return $count ? [
    		'status' => 'ok',
    		'elasticsearch' => Indices::stats([ 'trend', 'titles' ]),
    		'data' => $data
    	] : [
    		'status' => 'err'
    	];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Trend, options tablosu ayar güncelleme.
     *
     * @return array
     */
    public static function statusSet(SetRequest $request)
    {
        $count = Option::whereIn('key', [
            'trend.index'
        ])->where('value', 'on')->count();

        if ($count)
        {
            Option::updateOrCreate(
                [
                    'key' => $request->key
                ],
                [
                    'value' => $request->value
                ]
            );

            System::log('Trend durumu değiştirildi.', 'App\Http\Controllers\TrendController::statusSet('.$request->key.', '.$request->value.')', 1);
        }

        return [
            'status' => $count ? 'ok' : 'err'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Trend Logları
     *
     * @return array
     */
    public static function logJson()
    {
        $date = Carbon::now()->subHours(24)->format('Y-m-d H:i:s');

        $logs = Log::where('module', 'ILIKE', '%trend%')
                   ->where('updated_at', '>', $date)
                   ->orderBy('updated_at', 'DESC')
                   ->get();

        return [
            'status' => 'ok',
            'data' => $logs
        ];
    }
}
