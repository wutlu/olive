<?php

namespace App\Http\Controllers\Crawlers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests\IdRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SetRequest;

use App\Jobs\Elasticsearch\CreateYouTubeIndexJob;
use App\Jobs\Elasticsearch\DeleteIndexJob;

use App\Utilities\Crawler;

use App\Elasticsearch\Indices;
use App\Elasticsearch\Document;

use App\Models\Option;
use App\Models\Log;

use Carbon\Carbon;

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
    		'youtube.index.video',
    		'youtube.index.comment'
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
    # admin create index
    # 
    public static function indexCreate()
    {
        CreateYouTubeIndexJob::dispatch('video')->onQueue('elasticsearch');
        CreateYouTubeIndexJob::dispatch('comment')->onQueue('elasticsearch');

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
            'youtube.index.video',
            'youtube.index.comment'
        ])->where('value', 'on')->count();

    	return $count == 2 ? [ 'status' => 'ok', 'elasticsearch' => Indices::stats([ 'youtube', '*' ]) ] : [ 'status' => 'err' ];
    }

    # status set
    public static function statusSet(SetRequest $request)
    {
        $count = Option::whereIn('key', [
            'youtube.index.video',
            'youtube.index.comment'
        ])->where('value', 'on')->count();

        if ($count == 2)
        {
            Option::updateOrCreate(
                [
                    'key' => $request->key
                ],
                [
                    'value' => 'on'
                ]
            );
        }

        return [
            'status' => $count == 2 ? 'ok' : 'err'
        ];
    }

    # log ekranı data
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
}
