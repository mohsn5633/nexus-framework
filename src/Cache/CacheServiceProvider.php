<?php

namespace Nexus\Cache;

use Nexus\Core\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register the cache services
     */
    public function register(): void
    {
        $this->app->singleton(CacheManager::class, function ($app) {
            return new CacheManager($app->make('config'));
        });

        // Register alias
        $this->app->alias(CacheManager::class, 'cache');
    }

    /**
     * Boot the cache services
     */
    public function boot(): void
    {
        //
    }
}
