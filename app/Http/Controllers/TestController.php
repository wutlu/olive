<?php

namespace App\Http\Controllers;

class TestController extends Controller
{
    public static function test()
    {
        $site = 'http://www.yenierdekgazetesi.com';
        $cookie_path = storage_path('app/cookies/'.str_replace([ 'https', 'http', 'www' ], '', str_slug($site)).'.json');

        $cookieLine = [
            'Name' => 'asdasd',
            'Value' => 'gggasgasgsag',
            'Domain' => 'asdasd',
            'Path' => 'Asdasgsag',
            'Max-Age' => null,
            'Expires' => null,
            'Secure' => 'asgashgashg',
            'Discard' => false,
            'HttpOnly' => true,
            'SameSite' => 'Lax',
        ];

        $cookies = json_decode(file_get_contents($cookie_path));
        $cookies[] = $cookieLine;

        file_put_contents($cookie_path, json_encode($cookies), LOCK_EX);
    }
}
