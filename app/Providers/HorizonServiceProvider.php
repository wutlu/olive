<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class HorizonServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     */
    public function boot()
    {
        Horizon::auth(function ($request) {
            if (! $request->user()->root()) {
                throw new UnauthorizedHttpException('Unauthorized');
            }

            return true;
        });
    }

    /**
     * Register the application services.
     *
     */
    public function register()
    {
        //
    }
}
