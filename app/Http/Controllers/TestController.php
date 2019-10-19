<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Crawlers\MediaCrawler;
use System;
use Term;

class TestController extends Controller
{
    public static function test(Request $request)
    {
    	$cr = MediaCrawler::get();

    	foreach ($cr as $craw)
    	{
	    	$name = $craw->site;


	    	echo PHP_EOL;
    	}
    }
}
