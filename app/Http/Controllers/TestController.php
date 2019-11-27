<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Mail;

use App\Mail\NewsletterMail;

class TestController extends Controller
{
    public static function test(Request $request)
    {
    	try
    	{
            Mail::queue(
                new NewsletterMail(
                    'test',
                    'best acce',
                    'alper@veri.zone'
                )
            );
		}
		catch (\Exception $e)
		{
			print_r($e->getMessage());
		}
    }
}
