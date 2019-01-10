<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Session\DatabaseSessionHandler;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->session->extend('app.database', function ($app) {
            $lifetime = $this->app->config->get('session.lifetime');
            $table = $this->app->config->get('session.table');
            $connection = $app->app->db->connection($this->app->config->get('session.connection'));

            return new DatabaseSessionHandler($connection, $table, $lifetime, $this->app);
        });
    }
}
