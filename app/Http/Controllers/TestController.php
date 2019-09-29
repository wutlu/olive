<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Console\Commands\Alarm\Trigger;

class TestController extends Controller
{
    public static function test()
    {
    	return Trigger::handle();
    }
}
