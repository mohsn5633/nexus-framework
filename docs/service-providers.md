# Service Providers

Service providers are the central place for bootstrapping your application. They register services, bind interfaces to implementations, and perform any setup your application needs.

## Table of Contents

- [Introduction](#introduction)
- [Creating Providers](#creating-providers)
- [Registering Providers](#registering-providers)
- [Provider Lifecycle](#provider-lifecycle)
- [Built-in Providers](#built-in-providers)
- [Best Practices](#best-practices)

## Introduction

Service providers are classes that bootstrap application services during the application boot process. They have two main methods:

- **register()** - Register services into the container
- **boot()** - Perform actions after all services are registered

## Creating Providers

### Using CLI (Recommended)

```bash
php nexus make:provider PaymentServiceProvider
php nexus make:provider CacheServiceProvider
```

### Manual Creation

Create in `app/Providers/`:

```php
<?php

namespace App\Providers;

use Nexus\Core\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Bind services to container
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Perform initialization
    }
}
```

## Registering Providers

### In Configuration

Add providers to `config/app.php`:

```php
<?php

return [
    'providers' => [
        App\Providers\RouteServiceProvider::class,
        App\Providers\ViewServiceProvider::class,
        App\Providers\DatabaseServiceProvider::class,
        App\Providers\PaymentServiceProvider::class,
    ],
];
```

### Order Matters

Providers are registered in the order they appear. Ensure dependencies are registered first.

## Provider Lifecycle

### 1. Registration Phase

All providers' `register()` methods are called:

```php
public function register(): void
{
    // Bind services to container
    $this->app->singleton(PaymentGateway::class, function($app) {
        return new StripeGateway(
            config('services.stripe.key'),
            config('services.stripe.secret')
        );
    });
}
```

### 2. Boot Phase

After all services are registered, `boot()` methods are called:

```php
public function boot(): void
{
    // Use registered services
    $router = $this->app->make(Router::class);
    $router->middleware('auth', AuthMiddleware::class);
}
```

## Service Provider Examples

### Route Service Provider

```php
<?php

namespace App\Providers;

use Nexus\Core\ServiceProvider;
use Nexus\Http\Router;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register router service
        $this->app->singleton(Router::class, function($app) {
            return new Router($app);
        });
    }

    public function boot(): void
    {
        $router = $this->app->make(Router::class);

        // Register global middleware
        $router->middleware('maintenance', CheckForMaintenanceMode::class);

        // Load routes
        $this->loadRoutes();
    }

    protected function loadRoutes(): void
    {
        $routesFile = $this->app->basePath('bootstrap/routes.php');

        if (file_exists($routesFile)) {
            require $routesFile;
        }
    }
}
```

### View Service Provider

```php
<?php

namespace App\Providers;

use Nexus\Core\ServiceProvider;
use Nexus\View\View;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(View::class, function($app) {
            return new View(
                $app->basePath('app/Views'),
                $app->basePath('storage/framework/views')
            );
        });
    }

    public function boot(): void
    {
        // Share data with all views
        View::share('appName', config('app.name'));
        View::share('appUrl', config('app.url'));
        View::share('currentYear', date('Y'));
    }
}
```

### Database Service Provider

```php
<?php

namespace App\Providers;

use Nexus\Core\ServiceProvider;
use Nexus\Database\Database;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Database::class, function($app) {
            $config = $app->make('config');
            $connection = $config->get('database.default');
            $dbConfig = $config->get("database.connections.{$connection}");

            return new Database($dbConfig);
        });
    }

    public function boot(): void
    {
        // Database is lazy-loaded
        // No boot logic needed
    }
}
```

### Payment Service Provider

```php
<?php

namespace App\Providers;

use App\Services\PaymentGateway;
use App\Services\StripeGateway;
use Nexus\Core\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind interface to implementation
        $this->app->bind(PaymentGateway::class, function($app) {
            return new StripeGateway(
                config('services.stripe.key'),
                config('services.stripe.secret')
            );
        });
    }

    public function boot(): void
    {
        // Initialize payment gateway
        $gateway = $this->app->make(PaymentGateway::class);
        $gateway->initialize();
    }
}
```

### Cache Service Provider

```php
<?php

namespace App\Providers;

use App\Services\Cache;
use App\Services\FileCache;
use App\Services\RedisCache;
use Nexus\Core\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Cache::class, function($app) {
            $driver = config('cache.driver', 'file');

            return match($driver) {
                'redis' => new RedisCache(config('cache.redis')),
                default => new FileCache(storage_path('cache'))
            };
        });
    }

    public function boot(): void
    {
        // Cache is ready to use
    }
}
```

### Mail Service Provider

```php
<?php

namespace App\Providers;

use App\Services\Mailer;
use Nexus\Core\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Mailer::class, function($app) {
            return new Mailer([
                'host' => config('mail.host'),
                'port' => config('mail.port'),
                'username' => config('mail.username'),
                'password' => config('mail.password'),
                'from' => config('mail.from'),
            ]);
        });
    }

    public function boot(): void
    {
        // Mailer configuration
    }
}
```

## Binding Services

### Singleton Binding

Create a single instance:

```php
$this->app->singleton(Service::class, function($app) {
    return new Service();
});
```

### Basic Binding

Create new instance on each request:

```php
$this->app->bind(Service::class, function($app) {
    return new Service();
});
```

### Interface Binding

Bind interface to concrete implementation:

```php
$this->app->bind(PaymentInterface::class, StripePayment::class);
```

## Deferred Providers

Defer loading until service is actually needed:

```php
<?php

namespace App\Providers;

use Nexus\Core\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    protected bool $defer = true;

    public function register(): void
    {
        $this->app->singleton(Logger::class, function($app) {
            return new Logger(storage_path('logs'));
        });
    }

    public function provides(): array
    {
        return [Logger::class];
    }
}
```

## Complete Example

### Custom Service Provider

```php
<?php

namespace App\Providers;

use App\Services\{
    Analytics,
    PaymentGateway,
    NotificationService,
    StripeGateway
};
use Nexus\Core\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services
     */
    public function register(): void
    {
        // Bind payment gateway
        $this->app->singleton(PaymentGateway::class, function($app) {
            return new StripeGateway(
                config('services.stripe.key'),
                config('services.stripe.secret')
            );
        });

        // Bind analytics
        $this->app->singleton(Analytics::class, function($app) {
            return new Analytics(
                config('services.analytics.tracking_id')
            );
        });

        // Bind notification service
        $this->app->singleton(NotificationService::class, function($app) {
            return new NotificationService(
                $app->make('database'),
                $app->make('mailer')
            );
        });
    }

    /**
     * Bootstrap application services
     */
    public function boot(): void
    {
        // Share data with views
        View::share('analytics', $this->app->make(Analytics::class));

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->middleware('analytics', AnalyticsMiddleware::class);

        // Load custom configuration
        $this->loadCustomConfig();
    }

    /**
     * Load custom configuration
     */
    protected function loadCustomConfig(): void
    {
        $customConfig = $this->app->basePath('config/custom.php');

        if (file_exists($customConfig)) {
            $config = require $customConfig;
            $this->app->make('config')->set('custom', $config);
        }
    }
}
```

## Best Practices

1. **Register in register()**: Only bind services in register()
2. **Boot in boot()**: Use services in boot()
3. **Defer When Possible**: Use deferred providers for rarely used services
4. **Single Responsibility**: Each provider should handle related services
5. **Order Dependencies**: Register dependencies before dependents
6. **Use Interfaces**: Bind interfaces for better testability
7. **Configuration**: Use config files instead of hardcoding
8. **Documentation**: Document complex provider logic

## Common Patterns

### Factory Pattern

```php
public function register(): void
{
    $this->app->bind(ReportGenerator::class, function($app) {
        return ReportGeneratorFactory::create(
            config('reports.driver')
        );
    });
}
```

### Conditional Binding

```php
public function register(): void
{
    if (config('app.env') === 'local') {
        $this->app->singleton(Debugger::class, LocalDebugger::class);
    } else {
        $this->app->singleton(Debugger::class, ProductionDebugger::class);
    }
}
```

### Event Registration

```php
public function boot(): void
{
    Event::listen('user.created', function($user) {
        // Send welcome email
        Mail::send('emails.welcome', ['user' => $user]);
    });
}
```

## Next Steps

- Learn about [Dependency Injection](dependency-injection.md)
- Understand [Configuration](configuration.md)
- Explore [Middleware](middleware.md)
