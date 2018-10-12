<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SetRequest;

use App\Jobs\Elasticsearch\CreateGoogleIndexJob;
use App\Jobs\Elasticsearch\DeleteIndexJob;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

use App\Models\Option;
use App\Models\Log;

use Carbon\Carbon;

class GoogleController extends Controller
{
    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin list view
    # 
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

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin create index
    # 
    public static function indexCreate()
    {
        CreateGoogleIndexJob::dispatch()->onQueue('elasticsearch');

        return [
            'status' => 'ok'
        ];
    }

    # ######################################## [ ADMIN ] ######################################## #
    # 
    # admin index status
    # 
    public static function indexStatus()
    {
        $count = Option::whereIn('key', [
            'google.index.search'
        ])->where('value', 'on')->count();

    	return $count ? [ 'status' => 'ok', 'elasticsearch' => Indices::stats([ 'google', '*' ]) ] : [ 'status' => 'err' ];
    }

    # status set
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

    # log ekranı data
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
