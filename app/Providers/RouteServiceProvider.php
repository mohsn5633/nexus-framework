<?php

namespace App\Providers;

use Nexus\Core\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        // Auto-discover routes from controllers
        $this->app->router()->discoverRoutes(
            'App\\Controllers',
            $this->app->basePath('app/Controllers')
        );

        // Load routes file if exists
        $this->loadRoutes();
    }

    /**
     * Load the application routes
     */
    protected function loadRoutes(): void
    {
        // Load routes from routes directory
        $this->loadRoutesFrom($this->app->basePath('routes'));

        // Load legacy bootstrap/routes.php for backward compatibility
        $legacyRoutesFile = $this->app->basePath('bootstrap/routes.php');
        if (file_exists($legacyRoutesFile)) {
            require $legacyRoutesFile;
        }
    }

    /**
     * Load all route files from a directory
     */
    protected function loadRoutesFrom(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $routeFiles = glob($directory . '/*.php');

        foreach ($routeFiles as $routeFile) {
            require $routeFile;
        }
    }
}
