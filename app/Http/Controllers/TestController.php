<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Instagram;
use App\Utilities\DateUtility;
use App\Olive\Gender;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use App\Models\Twitter\Token;

class TestController extends Controller
{
    public static function test()
    {

    }
}
