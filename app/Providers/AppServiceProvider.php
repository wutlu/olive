<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use Request;
use App\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('recaptcha', function($attribute, $value, $parameters) {
            $link = 'https://www.google.com/recaptcha/api/siteverify?secret='.config('services.google.recaptcha.secret_key').'&response='.$value.'&remoteip='.Request::getClientIp();

            $source = file_get_contents($link);
            $json = json_decode($source);

            return $json->success;
        });

        Validator::extend('user_in_my_organisation', function($attribute, $user_id, $parameters) {
            $user = auth()->user();
            $friend = User::where('id', $user_id)->first();

            if (@$friend)
            {
                return ($user->organisation_id == $friend->organisation_id) ? true : false;
            }
            else
            {
                return false;
            }
        });

        Validator::extend('tckn', function($attribute, $value, $parameters) {
            $except = [
                '11111111110',
                '22222222220',
                '33333333330',
                '44444444440',
                '55555555550',
                '66666666660',
                '7777777770',
                '88888888880',
                '99999999990'
            ];

            if ($value[0] == 0 or !ctype_digit($value) or strlen($value) != 11)
            {
                return false;
            }
            else
            { 
                $ilkt = 0;
                $sont = 0;
                $tumt = 0;

                for($a = 0; $a < 9; $a = $a+2)
                {
                    $ilkt = $ilkt + $value[$a];
                }

                for($a = 1; $a < 9; $a = $a+2)
                {
                    $sont = $sont + $value[$a];
                }

                for($a = 0; $a < 10; $a = $a+1)
                {
                    $tumt = $tumt + $value[$a];
                }

                if (($ilkt*7-$sont)%10 != $value[9] or $tumt%10 != $value[10])
                {
                    return false;
                } 
                else
                {  
                    foreach($except as $ex)
                    {
                        if ($value == $ex)
                        {
                            return false;
                        }
                    }

                    return true; 
                } 
            }
        });

        Validator::extend('check_email_verification', function() {
            return auth()->user()->verified ? true : false;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
