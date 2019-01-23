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
    	$fee = auth()->user()->organisation->lastInvoice->fee();

    	echo $fee->total_price - $fee->amount_of_tax;
        return '';
    }
}
