# Package Development

## Overview

Packages in Nexus Framework provide a modular way to extend functionality and organize code. They are self-contained modules that can register services, routes, middleware, commands, and more. All packages are automatically discovered and loaded from the `packages/` directory.

## Package Structure

A typical package structure looks like this:

```
packages/
└── YourPackage/
    ├── Package.php          # Main package class (required)
    ├── Controllers/         # Package controllers
    ├── Models/             # Package models
    ├── Middleware/         # Package middleware
    ├── Commands/           # Package CLI commands
    ├── config/             # Package configuration files
    ├── routes/             # Package route definitions
    └── views/              # Package views
```

## Creating a Package

### Using CLI (Recommended)

The easiest way to create a package is using the built-in command:

```bash
php nexus make:package BlogEngine
```

This generates a complete package structure with the necessary boilerplate code.

### Manual Creation

1. Create a directory in `packages/` with your package name
2. Create a `Package.php` file extending `Nexus\Core\Package`

**Example:**

```php
<?php

namespace Packages\BlogEngine;

use Nexus\Core\Package as BasePackage;

class Package extends BasePackage
{
    /**
     * Register package services
     */
    public function register(): void
    {
        // Register services, bindings, singletons
    }

    /**
     * Boot package functionality
     */
    public function boot(): void
    {
        // Initialize routes, middleware, commands, etc.
    }
}
```

## Package Lifecycle

### Registration Phase

The `register()` method is called first and should be used for:

- Binding classes to the container
- Registering singletons
- Setting up service providers
- Merging configuration files

**Example:**

```php
public function register(): void
{
    // Bind an interface to implementation
    $this->app->bind(BlogRepositoryInterface::class, BlogRepository::class);

    // Register a singleton
    $this->app->singleton(BlogService::class, function ($app) {
        return new BlogService($app->make(BlogRepository::class));
    });

    // Merge package config with app config
    $this->mergeConfigFrom(__DIR__ . '/config/blog.php', 'blog');
}
```

### Boot Phase

The `boot()` method is called after all packages are registered and should be used for:

- Loading routes
- Registering middleware
- Registering CLI commands
- Publishing assets
- Setting up event listeners

**Example:**

```php
public function boot(): void
{
    // Load package routes
    $this->loadRoutes(__DIR__ . '/routes/web.php');

    // Register middleware
    $this->registerMiddleware();

    // Register CLI commands
    $this->registerCommands();

    // Publish configuration
    $this->publishes([
        __DIR__ . '/config/blog.php' => config_path('blog.php'),
    ], 'config');
}
```

## Common Package Tasks

### Registering Services

Use the container to bind services during registration:

```php
public function register(): void
{
    // Simple binding
    $this->app->bind(PaymentGateway::class, StripeGateway::class);

    // Singleton binding
    $this->app->singleton(CacheManager::class);

    // Binding with closure
    $this->app->bind(EmailService::class, function ($app) {
        return new EmailService(
            config('mail.driver'),
            $app->make(Logger::class)
        );
    });
}
```

### Loading Routes

Routes can be loaded using attribute-based routing or traditional route files:

```php
public function boot(): void
{
    // Load traditional routes
    if (file_exists(__DIR__ . '/routes/web.php')) {
        require __DIR__ . '/routes/web.php';
    }

    // Or use route groups
    $router = $this->app->make(\Nexus\Http\Router::class);
    $router->group(['prefix' => 'blog', 'namespace' => 'Packages\\BlogEngine\\Controllers'], function ($router) {
        require __DIR__ . '/routes/web.php';
    });
}
```

### Registering Middleware

```php
protected function registerMiddleware(): void
{
    $router = $this->app->make(\Nexus\Http\Router::class);

    // Register named middleware
    $router->middleware('blog.auth', \Packages\BlogEngine\Middleware\AuthMiddleware::class);
}
```

### Registering CLI Commands

```php
protected function registerCommands(): void
{
    if ($this->app->runningInConsole()) {
        $kernel = $this->app->make(\Nexus\Console\Kernel::class);

        $kernel->registerCommand(\Packages\BlogEngine\Commands\PublishPost::class);
        $kernel->registerCommand(\Packages\BlogEngine\Commands\SyncPosts::class);
    }
}
```

### Configuration Management

**Merging config during registration:**

```php
public function register(): void
{
    $this->mergeConfigFrom(__DIR__ . '/config/blog.php', 'blog');
}

protected function mergeConfigFrom(string $path, string $key): void
{
    $config = $this->app->make(\Nexus\Core\Config::class);
    $existing = $config->get($key, []);
    $package = require $path;

    $config->set($key, array_merge($package, $existing));
}
```

**Publishing config files:**

```php
public function boot(): void
{
    $this->publishes([
        __DIR__ . '/config/blog.php' => config_path('blog.php'),
    ], 'config');
}
```

### Loading Views

```php
public function boot(): void
{
    $view = $this->app->make(\Nexus\View\ViewFactory::class);
    $view->addNamespace('blog', __DIR__ . '/views');
}

// Usage in controller:
return view('blog::posts.index', ['posts' => $posts]);
```

### Database Migrations

```php
public function boot(): void
{
    $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
}

protected function loadMigrationsFrom(string $path): void
{
    // Implementation for loading migrations
    // This would integrate with your migration system
}
```

## Helper Methods

The `Package` base class provides several helper methods:

### Available in Register and Boot

- `$this->app` - Access the application container
- `$this->app->make($class)` - Resolve from container
- `$this->app->bind()` - Bind to container
- `$this->app->singleton()` - Register singleton

### Publishing Assets

```php
protected array $publishes = [];

protected function publishes(array $paths, string $group = null): void
{
    $this->publishes[$group ?? 'default'] = $paths;
}

// Usage
public function boot(): void
{
    $this->publishes([
        __DIR__ . '/config/blog.php' => config_path('blog.php'),
        __DIR__ . '/views' => resource_path('views/vendor/blog'),
    ], 'blog');
}
```

## Best Practices

### 1. Naming Conventions

- **Package directory**: PascalCase (e.g., `BlogEngine`, `PaymentGateway`)
- **Namespace**: Match directory name (e.g., `Packages\BlogEngine`)
- **Main class**: Always name it `Package.php`

### 2. Service Registration

- Register bindings in `register()`
- Boot services in `boot()`
- Use singletons for stateful services
- Type-hint dependencies for auto-resolution

### 3. Configuration

- Provide sensible defaults in package config
- Allow users to override via published config
- Use `env()` for environment-specific values
- Document all config options

### 4. Routes

- Use route prefixes to avoid conflicts
- Use named routes for URL generation
- Consider using route groups
- Apply middleware appropriately

### 5. Database

- Use migrations for schema changes
- Namespace model classes properly
- Consider using separate database connections
- Provide seeders for sample data

### 6. Dependencies

- Minimize external dependencies
- Document required packages
- Use composer for PHP dependencies
- Check dependency availability in boot

### 7. Testing

- Include tests with your package
- Test registration and booting
- Test all public APIs
- Mock external dependencies

## Complete Example: Blog Package

```php
<?php

namespace Packages\BlogEngine;

use Nexus\Core\Package as BasePackage;
use Nexus\Console\Kernel;
use Nexus\Http\Router;

class Package extends BasePackage
{
    /**
     * Register package services
     */
    public function register(): void
    {
        // Register repository
        $this->app->bind(
            Contracts\BlogRepositoryInterface::class,
            Repositories\BlogRepository::class
        );

        // Register service as singleton
        $this->app->singleton(Services\BlogService::class, function ($app) {
            return new Services\BlogService(
                $app->make(Contracts\BlogRepositoryInterface::class),
                $app->make(\Nexus\Core\Config::class)
            );
        });

        // Merge package configuration
        $this->mergeConfigFrom(__DIR__ . '/config/blog.php', 'blog');
    }

    /**
     * Boot package functionality
     */
    public function boot(): void
    {
        // Register routes
        $this->loadRoutes();

        // Register middleware
        $this->registerMiddleware();

        // Register commands
        $this->registerCommands();

        // Publish configuration
        $this->publishes([
            __DIR__ . '/config/blog.php' => config_path('blog.php'),
        ], 'config');

        // Publish views
        $this->publishes([
            __DIR__ . '/views' => resource_path('views/vendor/blog'),
        ], 'views');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Load package routes
     */
    protected function loadRoutes(): void
    {
        $router = $this->app->make(Router::class);

        $router->group([
            'prefix' => config('blog.route_prefix', 'blog'),
            'middleware' => ['web'],
        ], function ($router) {
            require __DIR__ . '/routes/web.php';
        });
    }

    /**
     * Register middleware
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $router->middleware('blog.auth', Middleware\BlogAuthMiddleware::class);
    }

    /**
     * Register CLI commands
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $kernel = $this->app->make(Kernel::class);

            $kernel->registerCommand(Commands\PublishPost::class);
            $kernel->registerCommand(Commands\ImportPosts::class);
            $kernel->registerCommand(Commands\GenerateSitemap::class);
        }
    }

    /**
     * Merge config from package
     */
    protected function mergeConfigFrom(string $path, string $key): void
    {
        $config = $this->app->make(\Nexus\Core\Config::class);
        $existing = $config->get($key, []);
        $package = require $path;

        $config->set($key, array_merge($package, $existing));
    }

    /**
     * Load migrations from path
     */
    protected function loadMigrationsFrom(string $path): void
    {
        // Migration loading implementation
        // This integrates with your migration system
    }
}
```

## Package Configuration Example

**packages/BlogEngine/config/blog.php:**

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for all blog routes
    |
    */
    'route_prefix' => env('BLOG_ROUTE_PREFIX', 'blog'),

    /*
    |--------------------------------------------------------------------------
    | Posts Per Page
    |--------------------------------------------------------------------------
    |
    | Number of posts to display per page
    |
    */
    'posts_per_page' => env('BLOG_POSTS_PER_PAGE', 10),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long to cache blog posts (in minutes)
    |
    */
    'cache_duration' => env('BLOG_CACHE_DURATION', 60),

    /*
    |--------------------------------------------------------------------------
    | Enable Comments
    |--------------------------------------------------------------------------
    |
    | Whether to enable comments on blog posts
    |
    */
    'enable_comments' => env('BLOG_ENABLE_COMMENTS', true),
];
```

## Debugging Packages

### Check if Package is Loaded

```php
// In Application.php or during boot
$packages = $this->getLoadedPackages();
var_dump($packages);
```

### Common Issues

1. **Package not loading**: Ensure `Package.php` exists in the package root
2. **Services not found**: Check if bindings are in `register()` not `boot()`
3. **Routes not working**: Verify route loading in `boot()` method
4. **Config not merging**: Ensure `mergeConfigFrom()` is called in `register()`

### Logging Package Activity

```php
public function boot(): void
{
    $logger = $this->app->make(\Nexus\Support\Logger::class);
    $logger->info('BlogEngine package booted');

    // Your boot logic here
}
```

## Package Distribution

### Preparing for Distribution

1. Document installation and usage
2. Include comprehensive README
3. Provide configuration examples
4. Include tests
5. Add changelog
6. Version your package

### Example README Structure

```markdown
# Blog Engine Package

## Installation

1. Place package in `packages/BlogEngine`
2. Package auto-loads on next request

## Configuration

Publish config:
```bash
php nexus vendor:publish --tag=blog-config
```

## Usage

[Usage examples]

## API Documentation

[API docs]
```

## Advanced Topics

### Package Dependencies

If your package depends on another package:

```php
public function register(): void
{
    // Check if required package is loaded
    if (!class_exists(\Packages\Authentication\Package::class)) {
        throw new \RuntimeException('BlogEngine requires Authentication package');
    }
}
```

### Event System Integration

```php
public function boot(): void
{
    // Register event listeners
    $events = $this->app->make(\Nexus\Events\Dispatcher::class);

    $events->listen(PostCreated::class, SendNotification::class);
    $events->listen(PostPublished::class, UpdateSitemap::class);
}
```

### Service Tags

Organize related services with tags:

```php
public function register(): void
{
    $this->app->bind(RssGenerator::class);
    $this->app->tag([RssGenerator::class], 'blog.exporters');

    $this->app->bind(JsonExporter::class);
    $this->app->tag([JsonExporter::class], 'blog.exporters');
}

// Later retrieve all tagged services
$exporters = $this->app->tagged('blog.exporters');
```

## See Also

- [Dependency Injection](dependency-injection.md)
- [Service Providers](service-providers.md)
- [Creating Commands](creating-commands.md)
- [Configuration](configuration.md)
- [Routing](routing.md)
