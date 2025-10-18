<?php

namespace Nexus\Core;

use Nexus\Database\Database;
use Nexus\Http\Router;
use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Support\View;

class Application extends Container
{
    protected static ?Application $instance = null;
    protected string $basePath;
    protected bool $booted = false;
    protected array $serviceProviders = [];
    protected array $loadedProviders = [];

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
        static::$instance = $this;

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
    }

    /**
     * Get the application instance
     */
    public static function getInstance(): self
    {
        return static::$instance;
    }

    /**
     * Register base bindings
     */
    protected function registerBaseBindings(): void
    {
        $this->instance('app', $this);
        $this->alias('app', Application::class);
    }

    /**
     * Register base service providers
     */
    protected function registerBaseServiceProviders(): void
    {
        // Register config
        $this->singleton('config', function ($app) {
            $config = new Config();
            $config->load($app->basePath('config'));
            return $config;
        });

        $this->alias('config', Config::class);

        // Register router
        $this->singleton('router', function ($app) {
            return new Router($app);
        });

        $this->alias('router', Router::class);

        // Register view
        $this->singleton('view', function ($app) {
            return new View($app->basePath('app/Views'));
        });

        $this->alias('view', View::class);

        // Register exception handler
        $this->singleton('exception.handler', function ($app) {
            return new ExceptionHandler($app);
        });

        $this->alias('exception.handler', ExceptionHandler::class);
    }

    /**
     * Bootstrap the application
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        // Load environment variables
        $this->loadEnvironment();

        // Register configured service providers
        $this->registerConfiguredProviders();

        // Boot all service providers
        $this->bootProviders();

        // Load packages
        $this->loadPackages();

        $this->booted = true;
    }

    /**
     * Register configured service providers
     */
    protected function registerConfiguredProviders(): void
    {
        $providers = $this->config()->get('app.providers', []);

        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Register a service provider
     */
    public function register(string|ServiceProvider $provider): ServiceProvider
    {
        // If already registered, return existing instance
        if ($registered = $this->getProvider($provider)) {
            return $registered;
        }

        // Resolve provider instance
        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        // Call register method
        $provider->register();

        // Mark as registered
        $this->serviceProviders[] = $provider;
        $this->loadedProviders[get_class($provider)] = true;

        return $provider;
    }

    /**
     * Boot all registered service providers
     */
    protected function bootProviders(): void
    {
        foreach ($this->serviceProviders as $provider) {
            $this->bootProvider($provider);
        }
    }

    /**
     * Boot a service provider
     */
    protected function bootProvider(ServiceProvider $provider): void
    {
        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
        }
    }

    /**
     * Get a registered provider instance
     */
    public function getProvider(string|ServiceProvider $provider): ?ServiceProvider
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        foreach ($this->serviceProviders as $value) {
            if ($value instanceof $name) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Load environment variables
     */
    protected function loadEnvironment(): void
    {
        $envFile = $this->basePath('.env');

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Parse key=value
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes
                $value = trim($value, '"\'');

                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    /**
     * Load packages
     */
    protected function loadPackages(): void
    {
        $packagesDir = $this->basePath('packages');

        if (!is_dir($packagesDir)) {
            return;
        }

        $packages = glob($packagesDir . '/*/Package.php');

        foreach ($packages as $packageFile) {
            $packageClass = 'Packages\\' . basename(dirname($packageFile)) . '\\Package';

            if (class_exists($packageClass)) {
                $package = new $packageClass();

                if (method_exists($package, 'register')) {
                    $package->register($this);
                }

                if (method_exists($package, 'boot')) {
                    $package->boot($this);
                }
            }
        }
    }

    /**
     * Handle an incoming HTTP request
     */
    public function handle(Request $request): Response
    {
        if (!$this->booted) {
            $this->boot();
        }

        try {
            // Check for maintenance mode
            $maintenanceMiddleware = $this->make(\Nexus\Http\Middleware\CheckForMaintenanceMode::class);
            $response = $maintenanceMiddleware->handle($request, function($req) {
                return $this->router()->dispatch($req);
            });

            return $response;
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Handle an exception
     */
    protected function handleException(\Throwable $e): Response
    {
        /** @var ExceptionHandler $handler */
        $handler = $this->make('exception.handler');

        // Log the exception
        $handler->log($e);

        // Render error response
        return $handler->handle($e);
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        $request = Request::capture();
        $response = $this->handle($request);
        $response->send();
    }

    /**
     * Get the base path
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    /**
     * Get the config instance
     */
    public function config(): Config
    {
        return $this->make('config');
    }

    /**
     * Get the router instance
     */
    public function router(): Router
    {
        return $this->make('router');
    }

    /**
     * Get the database instance
     */
    public function db(): Database
    {
        return $this->make('db');
    }
}
