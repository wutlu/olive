<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SetRequest;

use App\Jobs\Elasticsearch\CreateGoogleIndexJob;

use App\Elasticsearch\Indices;

use App\Models\Option;
use App\Models\Log;

use Carbon\Carbon;

class GoogleController extends Controller
{
    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Google, durum yönetimi ana sayfası.
     *
     * @return view
     */
    public static function dashboard()
    {
    	$rows = Option::whereIn('key', [
    		'google.status',
    		'google.index.search'
    	])->get();

    	$options = [];

    	foreach ($rows as $row)
    	{
    		$options[$row->key] = $row->value;
    	}

        return view('crawlers.google.dashboard', compact('options'));
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Google trendler, Elasticsearch index oluşturma tetikleyicisi.
     *
     * @return array
     */
    public static function indexCreate()
    {
        CreateGoogleIndexJob::dispatch()->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Google Trendler, Elasticsearch index durumu.
     * - Index oluşturuldu mu, oluşturulmadı mı kontrolünü sağlar.
     *
     * @return array
     */
    public static function indexStatus()
    {
        $count = Option::whereIn('key', [
            'google.index.search'
        ])->where('value', 'on')->count();

    	return $count ? [ 'status' => 'ok', 'elasticsearch' => Indices::stats([ 'google', '*' ]) ] : [ 'status' => 'err' ];
    }

    /**
     ********************
     ******* ROOT *******
     ********************
     *
     * Google, options tablosu ayar güncelleme.
     *
     * @return array
     */
    public static function statusSet(SetRequest $request)
    {
        $count = Option::whereIn('key', [
            'google.index.search'
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
     * Google Logları
     *
     * @return array
     */
    public static function logJson()
    {
        $date = Carbon::now()->subHours(24)->format('Y-m-d H:i:s');

        $logs = Log::where('module', 'ILIKE', '%google%')
                   ->where('updated_at', '>', $date)
                   ->orderBy('updated_at', 'DESC')
                   ->get();

        return [
            'status' => 'ok',
            'data' => $logs
        ];
    }
}
