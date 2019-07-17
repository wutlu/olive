<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Instagram;

class TestController extends Controller
{
    public static function test()
    {
        $url = 'explore/tags/ankara/';
        $url = 'explore/locations/215088589/ankara-turkey/';
        $url = 'veri8zone';
        $url = 'wutlu.php';

        $instagram = new Instagram;
        $connect = $instagram->connect($url);

        if ($connect->status == 'ok')
        {
            $data = $instagram->data('user');

            if ($data->status == 'ok')
            {
                echo "<pre>";
                print_r($data->user);
                print_r($data->data);
            }
            else
            {
                echo $data->message;
            }
        }
        else
        {
            echo $connect->code;
        }
    }
}
