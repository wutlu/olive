<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Elasticsearch\Document;

use Carbon\Carbon;

class TestController extends Controller
{
    public static function test()
    {
    	return view('test');
    }
}
