<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User\User;
use System;

class TestController extends Controller
{
    public static function test()
    {
            $selectors = \App\Models\Crawlers\MediaCrawler::select('selector_title', \DB::raw('count(*) as total'))
                                     ->whereNotNull('selector_title')
                                     ->groupBy('selector_title')
                                     ->orderBy('total', 'DESC')
                                     ->get();

            echo count($selectors);
    }
}
