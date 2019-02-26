<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Elasticsearch\Document;

use Carbon\Carbon;

class TestController extends Controller
{
    public static function test()
    {
        echo 'HTTP_X_FORWARDED_FOR:';
        echo @$_SERVER['HTTP_X_FORWARDED_FOR'];
        echo '<br />';
        echo 'HTTP_CLIENT_IP:';
        echo @$_SERVER['HTTP_CLIENT_IP'];
        echo '<br />';
        echo 'REMOTE_ADDR:';
        echo @$_SERVER['REMOTE_ADDR'];
    }
}
