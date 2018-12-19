<?php

namespace BunnyCDN\API;

use BunnyCDN\API\APIClient as BunnyCDNAPI;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Foundation\Application as LaravelApplication;

class BunnyCDNAPIServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton (BunnyCDNAPI::class, function () {
            return new BunnyCDNAPI::class;
        });
    }

}
