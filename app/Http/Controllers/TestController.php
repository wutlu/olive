<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Mail;

class TestController extends Controller
{
    public static function test(Request $request)
    {
        $phone = '(538) 394 96 93';

        if (!preg_match('/\(\d{3}\) \d{3} \d{2} \d{2}/i', $phone))
        {
            echo 'err';
        }
        else
        {
            echo "username ok";
        }
    }
}
