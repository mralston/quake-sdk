<?php

namespace Mralston\Quake\Providers;

use Illuminate\Support\ServiceProvider;
use Mralston\Quake\Client;

class QuakeServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client(
                config('quake.username'),
                config('quake.password'),
                config('quake.company_id'),
                config('quake.api_endpoint')
            );
        });

        $this->publishes([
            __DIR__.'/../../config/quake.php' => config_path('quake.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/quake.php', 'quake');
    }
}