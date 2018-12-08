<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::pattern('key', '[a-zA-Z0-9-\/:=_]+');
        Route::pattern('id', '[0-9]+');
        Route::pattern('step', '[0-9]+');
        Route::pattern('user_id', '[0-9]+');
        Route::pattern('slug', '[a-zA-Z0-9-_]+');
        Route::pattern('name', '[a-zA-Z0-9-\._]+');
        Route::pattern('sid', '[a-zA-Z0-9-_]+');
        Route::pattern('skip', '[0-9]+');
        Route::pattern('take', '[0-9]{1,2}');
        Route::pattern('status', '(true|false)');

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapRootRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "root" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapRootRoutes()
    {
        Route::middleware([ 'web', 'root' ])
             ->namespace($this->namespace)
             ->prefix('root')
             ->group(base_path('routes/root.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
