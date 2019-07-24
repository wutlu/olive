<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Instagram;
use App\Utilities\DateUtility;

class TestController extends Controller
{
    public static function test()
    {
        $instagram = new Instagram;
        $connect = $instagram->connect('https://www.instagram.com/wutlu.php/');

        if ($connect->status == 'ok')
        {
            $data = $instagram->data('user');

            if ($data->status == 'ok')
            {
                echo $connect->dom;
                exit();
                print_r($data->data);
            }
            else
            {
            	echo 'hata 1:';
                // log girilecek echo $data->message;
                echo $data->message;
            }
        }
        else
        {
            echo 'hata 2:';
            // log girilecek echo $connect->code;
            echo $connect->code;
        }
    }
}
