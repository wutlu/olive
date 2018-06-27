<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use Auth;
use Request;

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
