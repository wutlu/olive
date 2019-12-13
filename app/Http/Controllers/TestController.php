<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Notifications\MessageNotification;
use App\Models\User\User;

class TestController extends Controller
{
    public static function test(Request $request)
    {
        $user = User::where('id', 1)->first();

        $user->notify(
            (
                new MessageNotification(
                    'asd',
                    'best asd',
                    'asfasf asfasfsaf'
                )
            )->onQueue('email')
        );
    }
}
