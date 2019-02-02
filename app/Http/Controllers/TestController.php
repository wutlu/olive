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
use App\Elasticsearch\Document;

class TestController extends Controller
{
    public static function test()
    {
$ip = gethostbyname('www.haber7.com');

echo $ip;
    }
}
