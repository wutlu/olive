<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utilities\DateUtility;

class TestController extends Controller
{
    public static function test()
    {
    	print_r(DateUtility::getDateInDom('2018-12-10T15:44:00.000Z'));
    	exit();
    }
}
