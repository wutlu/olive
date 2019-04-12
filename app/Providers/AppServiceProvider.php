<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Validator;
use Request;
use Hash;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        URL::forceScheme(config('app.ssl') ? 'https' : 'http');

        Validator::extend('recaptcha', function($attribute, $value, $parameters) {
            $link = 'https://www.google.com/recaptcha/api/siteverify?secret='.config('services.google.recaptcha.secret_key').'&response='.$value.'&remoteip='.Request::getClientIp();

            $source = file_get_contents($link);
            $json = json_decode($source);

            return $json->success;
        });

        Validator::extend('iban', function($attribute, $iban, $parameters) {
            $iban = strtolower(str_replace(' ', '', $iban));
            $countries = [
                'al' => 28,
                'ad' => 24,
                'at' => 20,
                'az' => 28,
                'bh' => 22,
                'be' => 16,
                'ba' => 20,
                'br' => 29,
                'bg' => 22,
                'cr' => 21,
                'hr' => 21,
                'cy' => 28,
                'cz' => 24,
                'dk' => 18,
                'do' => 28,
                'ee' => 20,
                'fo' => 18,
                'fi' => 18,
                'fr' => 27,
                'ge' => 22,
                'de' => 22,
                'gi' => 23,
                'gr' => 27,
                'gl' => 18,
                'gt' => 28,
                'hu' => 28,
                'is' => 26,
                'ie' => 22,
                'il' => 23,
                'it' => 27,
                'jo' => 30,
                'kz' => 20,
                'kw' => 30,
                'lv' => 21,
                'lb' => 28,
                'li' => 21,
                'lt' => 20,
                'lu' => 20,
                'mk' => 19,
                'mt' => 31,
                'mr' => 27,
                'mu' => 30,
                'mc' => 27,
                'md' => 24,
                'me' => 22,
                'nl' => 18,
                'no' => 15,
                'pk' => 24,
                'ps' => 29,
                'pl' => 28,
                'pt' => 25,
                'qa' => 29,
                'ro' => 24,
                'sm' => 27,
                'sa' => 24,
                'rs' => 22,
                'sk' => 24,
                'si' => 19,
                'es' => 24,
                'se' => 24,
                'ch' => 21,
                'tn' => 24,
                'tr' => 26,
                'ae' => 23,
                'gb' => 22,
                'vg' => 24
            ];
            $chars = [
                'a' => 10,
                'b' => 11,
                'c' => 12,
                'd' => 13,
                'e' => 14,
                'f' => 15,
                'g' => 16,
                'h' => 17,
                'i' => 18,
                'j' => 19,
                'k' => 20,
                'l' => 21,
                'm' => 22,
                'n' => 23,
                'o' => 24,
                'p' => 25,
                'q' => 26,
                'r' => 27,
                's' => 28,
                't' => 29,
                'u' => 30,
                'v' => 31,
                'w' => 32,
                'x' => 33,
                'y' => 34,
                'z' => 35
            ];

            if (strlen($iban) == $countries[substr($iban, 0, 2)])
            {
                $MovedChar = substr($iban, 4).substr($iban, 0, 4);
                $MovedCharArray = str_split($MovedChar);
                $NewString = '';

                foreach($MovedCharArray AS $key => $value)
                {
                    if (!is_numeric($MovedCharArray[$key]))
                    {
                        $MovedCharArray[$key] = $chars[$MovedCharArray[$key]];
                    }

                    $NewString .= $MovedCharArray[$key];
                }

                return bcmod($NewString, '97') == 1 ? true : false;
            }
            else
            {
                return false;
            }
        });

        Validator::extend('except_list', function($attribute, $keyword) {
            return !in_array(str_slug($keyword, ' '), config('services.twitter.unaccepted_keywords'));
        });

        Validator::extend('password_check', function($attribute, $value) {
            return Hash::check($value , auth()->user()->password);
        });

        Validator::extend('slug', function($attribute, $value) {
            return !preg_match('/[^a-z0-9\-]/', $value);
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
