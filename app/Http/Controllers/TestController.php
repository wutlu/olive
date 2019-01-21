<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Utilities\DateUtility;
use App\Utilities\Crawler;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use App\Wrawler;

use Carbon\Carbon;

use App\Models\Crawlers\MediaCrawler;

class TestController extends Controller
{
    public static function test()
    {
        $counts = [];

        foreach (config('database.elasticsearch.media.groups') as $group)
        {
            $counts[$group] = MediaCrawler::where('elasticsearch_index_name', $group)->count();
        }

        $sorted = array_sort($counts);

        return array_keys($sorted)[0];
    }
}
