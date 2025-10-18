<?php

namespace App\Providers;

use Nexus\Core\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services
     */
    public function register(): void
    {
        // Register application services here
        // Example:
        // $this->app->singleton('service', function ($app) {
        //     return new Service();
        // });
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        // Bootstrap application services here
        // Example:
        // View::share('app_name', config('app.name'));
    }
}
