<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Term;
use App\SMS;

class TestController extends Controller
{
    public static function test()
    {
    	return SMS::send('asd', [ '905383949693' ]);
	}
}
