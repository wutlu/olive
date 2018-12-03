<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Validator;
use Request;
use Hash;

use Illuminate\Support\Facades\Schema;

use App\Models\Discount\DiscountCoupon;

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

        Validator::extend('recaptcha', function($attribute, $value, $parameters) {
            $link = 'https://www.google.com/recaptcha/api/siteverify?secret='.config('services.google.recaptcha.secret_key').'&response='.$value.'&remoteip='.Request::getClientIp();

            $source = file_get_contents($link);
            $json = json_decode($source);

            return $json->success;
        });

        Validator::extend('password_check', function($attribute, $value) {
            return Hash::check($value , auth()->user()->password);
        });

        Validator::extend('coupon_exists', function($attribute, $key) {
            return DiscountCoupon::whereNull('invoice_id')->where('key', $key)->count();
        });

        Validator::extend('organisation_status', function($attribute) {
            return auth()->user()->organisation->status == true;
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
