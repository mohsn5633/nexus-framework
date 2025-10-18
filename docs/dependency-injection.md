# Dependency Injection

Nexus Framework provides a powerful Dependency Injection (DI) container that automatically resolves class dependencies through constructor and method injection.

## Table of Contents

- [Introduction](#introduction)
- [Constructor Injection](#constructor-injection)
- [Method Injection](#method-injection)
- [Binding Services](#binding-services)
- [Resolving Dependencies](#resolving-dependencies)
- [Practical Examples](#practical-examples)

## Introduction

Dependency Injection is a design pattern that removes hard-coded dependencies and makes it possible to change them, either at run-time or compile-time.

### Benefits

- **Testability**: Easily mock dependencies in tests
- **Flexibility**: Swap implementations without changing code
- **Maintainability**: Clearer dependencies and responsibilities
- **Reusability**: Services can be reused across the application

## Container Basics

### The Application Container

The application container is responsible for managing class dependencies and performing dependency injection:

```php
// Get the container
$container = app();

// Resolve a class
$service = $container->make(UserService::class);

// Bind a service
$container->singleton(PaymentGateway::class, StripeGateway::class);
```

## Constructor Injection

### Basic Constructor Injection

Dependencies are automatically injected into constructors:

```php
<?php

namespace App\Services;

use Nexus\Database\Database;

class UserService
{
    public function __construct(
        protected Database $db
    ) {
    }

    public function getAllUsers(): array
    {
        return $this->db->table('users')->get();
    }
}
```

Usage in controllers:

```php
<?php

namespace App\Controllers;

use App\Services\UserService;
use Nexus\Http\Request;
use Nexus\Http\Response;

class UserController
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    #[Get('/users', 'users.index')]
    public function index(Request $request): Response
    {
        $users = $this->userService->getAllUsers();
        return Response::json($users);
    }
}
```

### Multiple Dependencies

Inject multiple dependencies:

```php
<?php

namespace App\Services;

use Nexus\Database\Database;
use App\Services\Cache;
use App\Services\Logger;

class ProductService
{
    public function __construct(
        protected Database $db,
        protected Cache $cache,
        protected Logger $logger
    ) {
    }

    public function getProduct(int $id): ?array
    {
        $cacheKey = "product:{$id}";

        // Try cache first
        if ($cached = $this->cache->get($cacheKey)) {
            $this->logger->info("Product {$id} loaded from cache");
            return $cached;
        }

        // Load from database
        $product = $this->db->table('products')->find($id);

        if ($product) {
            $this->cache->set($cacheKey, $product, 3600);
            $this->logger->info("Product {$id} loaded from database");
        }

        return $product;
    }
}
```

## Method Injection

### Controller Method Injection

Dependencies can be injected into controller methods:

```php
<?php

namespace App\Controllers;

use App\Services\UserService;
use Nexus\Database\Database;
use Nexus\Http\Request;
use Nexus\Http\Response;

class UserController
{
    #[Get('/users', 'users.index')]
    public function index(Request $request, UserService $userService): Response
    {
        $users = $userService->getAllUsers();
        return Response::json($users);
    }

    #[Get('/users/{id}', 'users.show')]
    public function show(Request $request, int $id, Database $db): Response
    {
        $user = $db->table('users')->find($id);
        return Response::json($user);
    }
}
```

### Route Parameters + Dependencies

Combine route parameters with dependency injection:

```php
#[Get('/users/{id}/orders', 'users.orders')]
public function orders(
    Request $request,
    int $id,
    OrderService $orderService
): Response {
    $orders = $orderService->getUserOrders($id);
    return Response::json($orders);
}
```

## Binding Services

### Simple Binding

Bind a class to the container:

```php
// In a service provider
$this->app->bind(PaymentGateway::class, StripeGateway::class);
```

### Singleton Binding

Create a single instance shared across the application:

```php
$this->app->singleton(Database::class, function($app) {
    return new Database(config('database'));
});
```

### Closure Binding

Use a closure for custom instantiation:

```php
$this->app->bind(Mailer::class, function($app) {
    return new Mailer([
        'host' => config('mail.host'),
        'port' => config('mail.port'),
        'username' => config('mail.username'),
        'password' => config('mail.password')
    ]);
});
```

### Interface Binding

Bind interfaces to implementations:

```php
// Bind interface to concrete class
$this->app->bind(
    PaymentGatewayInterface::class,
    StripeGateway::class
);

// Now you can type-hint the interface
public function __construct(PaymentGatewayInterface $gateway) {
    $this->gateway = $gateway;
}
```

## Resolving Dependencies

### Using make()

Resolve a class from the container:

```php
$service = app()->make(UserService::class);
$users = $service->getAllUsers();
```

### Using app() Helper

```php
$service = app(UserService::class);
$users = $service->getAllUsers();
```

### Automatic Resolution

Classes are automatically resolved when injected:

```php
// No need to manually resolve
public function __construct(UserService $service) {
    // $service is automatically resolved
}
```

## Practical Examples

### Service Layer Pattern

```php
<?php

// Service Interface
namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function charge(float $amount, array $card): bool;
    public function refund(string $transactionId): bool;
}

// Stripe Implementation
namespace App\Services;

use App\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    public function __construct(
        protected string $apiKey,
        protected string $apiSecret
    ) {
    }

    public function charge(float $amount, array $card): bool
    {
        // Stripe charge logic
        return true;
    }

    public function refund(string $transactionId): bool
    {
        // Stripe refund logic
        return true;
    }
}

// Service Provider
namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\StripeGateway;
use Nexus\Core\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayInterface::class, function($app) {
            return new StripeGateway(
                config('services.stripe.key'),
                config('services.stripe.secret')
            );
        });
    }
}

// Controller Usage
namespace App\Controllers;

use App\Contracts\PaymentGatewayInterface;
use Nexus\Http\Request;
use Nexus\Http\Response;

class PaymentController
{
    public function __construct(
        protected PaymentGatewayInterface $gateway
    ) {
    }

    #[Post('/payments', 'payments.charge')]
    public function charge(Request $request): Response
    {
        $validated = validate($request->all(), [
            'amount' => 'required|numeric|min:1',
            'card' => 'required|array'
        ]);

        $success = $this->gateway->charge(
            $validated['amount'],
            $validated['card']
        );

        return Response::json([
            'success' => $success
        ]);
    }
}
```

### Repository Pattern

```php
<?php

// Repository Interface
namespace App\Repositories;

interface UserRepositoryInterface
{
    public function find(int $id): ?array;
    public function all(): array;
    public function create(array $data): array;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}

// Implementation
namespace App\Repositories;

use Nexus\Database\Database;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        protected Database $db
    ) {
    }

    public function find(int $id): ?array
    {
        return $this->db->table('users')->find($id);
    }

    public function all(): array
    {
        return $this->db->table('users')->get();
    }

    public function create(array $data): array
    {
        $id = $this->db->table('users')->insert($data);
        return $this->find($id);
    }

    public function update(int $id, array $data): bool
    {
        return $this->db->table('users')
            ->where('id', '=', $id)
            ->update($data) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->table('users')->delete($id) > 0;
    }
}

// Service Provider
namespace App\Providers;

use App\Repositories\{UserRepository, UserRepositoryInterface};
use Nexus\Core\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
    }
}

// Service Using Repository
namespace App\Services;

use App\Repositories\UserRepositoryInterface;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function getActiveUsers(): array
    {
        $users = $this->userRepository->all();
        return array_filter($users, fn($user) => $user['status'] === 'active');
    }

    public function createUser(array $data): array
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['created_at'] = date('Y-m-d H:i:s');

        return $this->userRepository->create($data);
    }
}
```

### Event System

```php
<?php

// Event Dispatcher
namespace App\Services;

class EventDispatcher
{
    protected array $listeners = [];

    public function listen(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function dispatch(string $event, $data = null): void
    {
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                $listener($data);
            }
        }
    }
}

// Service Provider
namespace App\Providers;

use App\Services\EventDispatcher;
use Nexus\Core\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EventDispatcher::class, function() {
            return new EventDispatcher();
        });
    }

    public function boot(): void
    {
        $events = $this->app->make(EventDispatcher::class);

        // Register listeners
        $events->listen('user.created', function($user) {
            // Send welcome email
        });

        $events->listen('user.deleted', function($user) {
            // Clean up user data
        });
    }
}

// Usage in Service
namespace App\Services;

class UserService
{
    public function __construct(
        protected UserRepository $repository,
        protected EventDispatcher $events
    ) {
    }

    public function createUser(array $data): array
    {
        $user = $this->repository->create($data);

        // Dispatch event
        $this->events->dispatch('user.created', $user);

        return $user;
    }
}
```

## Best Practices

1. **Type Hint Interfaces**: Depend on abstractions, not concretions
2. **Constructor Injection**: Prefer constructor injection for required dependencies
3. **Single Responsibility**: Keep services focused on one task
4. **Avoid Service Locator**: Use injection instead of resolving from container
5. **Bind in Providers**: Register bindings in service providers
6. **Use Singletons Wisely**: Singleton for stateless services only
7. **Document Dependencies**: Clear PHPDoc for complex dependencies
8. **Test with Mocks**: Easy to mock injected dependencies

## Common Patterns

### Factory Pattern

```php
class ReportGeneratorFactory
{
    public static function create(string $type): ReportGenerator
    {
        return match($type) {
            'pdf' => app(PdfReportGenerator::class),
            'excel' => app(ExcelReportGenerator::class),
            'csv' => app(CsvReportGenerator::class),
            default => throw new \InvalidArgumentException("Unknown type: {$type}")
        };
    }
}
```

### Strategy Pattern

```php
interface ShippingStrategy
{
    public function calculateCost(float $weight): float;
}

class StandardShipping implements ShippingStrategy
{
    public function calculateCost(float $weight): float
    {
        return $weight * 2.5;
    }
}

class ExpressShipping implements ShippingStrategy
{
    public function calculateCost(float $weight): float
    {
        return $weight * 5.0;
    }
}

class ShippingService
{
    public function __construct(
        protected ShippingStrategy $strategy
    ) {
    }

    public function getShippingCost(float $weight): float
    {
        return $this->strategy->calculateCost($weight);
    }
}
```

## Next Steps

- Learn about [Service Providers](service-providers.md)
- Understand [Controllers](controllers.md)
- Explore [Middleware](middleware.md)
