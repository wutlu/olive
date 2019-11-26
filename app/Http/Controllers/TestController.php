<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Email;

class TestController extends Controller
{
    public static function test(Request $request)
    {
        print_r(Email::detector('asda alper@veri.zone incuba.city@mail.sehir.edu.tr asdsad@2x.jpg  header-logo@2x.png sd'));
    }
}
