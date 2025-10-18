<?php

namespace Nexus\Core;

abstract class ServiceProvider
{
    public function __construct(
        protected Application $app
    ) {
    }

    /**
     * Register any application services
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [];
    }
}
