<?php

namespace App\Providers;

use Nexus\Core\ServiceProvider;
use Nexus\Database\Database;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services
     */
    public function register(): void
    {
        $this->app->singleton('db', function ($app) {
            $connection = $app->config()->get('database.default');
            $config = $app->config()->get("database.connections.$connection");
            return new Database($config);
        });

        $this->app->alias('db', Database::class);
    }
}
