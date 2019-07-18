<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Instagram;

class TestController extends Controller
{
    public static function test()
    {
                $instagram = new Instagram;
                $connect = $instagram->connect('https://www.instagram.com/ekremimamoglu/');

                if ($connect->status == 'ok')
                {
                    $data = $instagram->data('user');

                    if ($data->status == 'ok')
                    {
                        print_r($data->user);
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
