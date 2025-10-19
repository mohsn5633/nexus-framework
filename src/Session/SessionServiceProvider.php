<?php

namespace Nexus\Session;

use Nexus\Core\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register the session services
     */
    public function register(): void
    {
        $this->app->singleton(SessionManager::class, function ($app) {
            return new SessionManager($app->make('config'));
        });

        // Register alias
        $this->app->alias(SessionManager::class, 'session');
    }

    /**
     * Boot the session services
     */
    public function boot(): void
    {
        //
    }
}
