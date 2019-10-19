<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\User\User;
use System;

class TestController extends Controller
{
    public static function test(Request $request)
    {
		$dizge = 'nth-child(1)';
		$sablon = '/nth-child\(\d+\)/i';
		$yenisi = 'nth-child(test)';
		echo preg_replace($sablon, $yenisi, $dizge);
    }
}
