# Middleware

Middleware provides a convenient mechanism for filtering HTTP requests entering your application. They act as a bridge between a request and a response.

## Table of Contents

- [Introduction](#introduction)
- [Creating Middleware](#creating-middleware)
- [Registering Middleware](#registering-middleware)
- [Applying Middleware](#applying-middleware)
- [Middleware Parameters](#middleware-parameters)
- [Built-in Middleware](#built-in-middleware)
- [Middleware Pipeline](#middleware-pipeline)
- [Common Use Cases](#common-use-cases)

## Introduction

Middleware runs before your controller methods, allowing you to:
- Authenticate users
- Validate CSRF tokens
- Log requests
- Modify request/response
- Handle CORS
- Rate limiting
- And much more

### How Middleware Works

```
Request → Middleware 1 → Middleware 2 → Controller → Response
                                                      ↓
Response ← Middleware 1 ← Middleware 2 ← Controller ←
```

## Creating Middleware

### Using CLI (Recommended)

Generate a new middleware:

```bash
php nexus make:middleware AuthMiddleware
php nexus make:middleware CorsMiddleware
php nexus make:middleware LogRequestMiddleware
```

### Manual Creation

Create a file in `app/Middleware/`:

```php
<?php

namespace App\Middleware;

use Closure;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class AuthMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Code before controller execution

        $response = $next($request);

        // Code after controller execution

        return $response;
    }
}
```

## Basic Middleware Structure

### Simple Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class SimpleMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Perform action before request

        return $next($request);
    }
}
```

### Before & After Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class BeforeAfterMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Before: Runs before the controller
        $startTime = microtime(true);

        // Execute the next middleware/controller
        $response = $next($request);

        // After: Runs after the controller
        $duration = microtime(true) - $startTime;
        error_log("Request took: {$duration} seconds");

        return $response;
    }
}
```

## Registering Middleware

Middleware must be registered before use.

### Global Middleware

Register in `src/Core/Application.php` or create a middleware configuration file:

```php
// config/middleware.php
<?php

return [
    'global' => [
        \App\Middleware\LogRequestMiddleware::class,
        \App\Middleware\CorsMiddleware::class,
    ],

    'aliases' => [
        'auth' => \App\Middleware\AuthMiddleware::class,
        'guest' => \App\Middleware\GuestMiddleware::class,
        'admin' => \App\Middleware\AdminMiddleware::class,
        'csrf' => \App\Middleware\CsrfMiddleware::class,
    ],
];
```

### Middleware Aliases

Create short aliases for middleware:

```php
protected array $middlewareAliases = [
    'auth' => AuthMiddleware::class,
    'guest' => GuestMiddleware::class,
    'admin' => AdminMiddleware::class,
    'verified' => EmailVerifiedMiddleware::class,
    'throttle' => ThrottleMiddleware::class,
];
```

## Applying Middleware

### On Route (Attribute)

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Route\Get;
use Nexus\Http\Route\Middleware;

class DashboardController
{
    #[Get('/dashboard', 'dashboard')]
    #[Middleware('auth')]
    public function index(Request $request): Response
    {
        return Response::view('dashboard');
    }

    #[Get('/admin', 'admin.dashboard')]
    #[Middleware(['auth', 'admin'])]
    public function admin(Request $request): Response
    {
        return Response::view('admin.dashboard');
    }
}
```

### On Controller Class

Apply middleware to all methods in a controller:

```php
<?php

namespace App\Controllers;

use Nexus\Http\Route\Middleware;

#[Middleware(['auth'])]
class UserController
{
    // All methods inherit 'auth' middleware

    #[Get('/users', 'users.index')]
    public function index(Request $request): Response
    {
        // Requires authentication
    }

    #[Get('/users/{id}', 'users.show')]
    #[Middleware(['verified'])]  // Additional middleware
    public function show(Request $request, int $id): Response
    {
        // Requires authentication + verification
    }
}
```

### On Routes (File-Based)

```php
<?php

use Nexus\Http\Router;

/** @var Router $router */

// Single middleware
$router->get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');

// Multiple middleware
$router->post('/users', [UserController::class, 'store'])
    ->middleware(['auth', 'csrf', 'admin']);

// Middleware on route groups
$router->group(['middleware' => ['auth']], function ($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->get('/profile', [ProfileController::class, 'show']);
});
```

## Middleware Parameters

Pass parameters to middleware:

### Defining Parameters

```php
<?php

namespace App\Middleware;

use Closure;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class RoleMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (!$user || !in_array($user->role, $roles)) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
```

### Using Parameters

```php
#[Get('/admin', 'admin')]
#[Middleware('role:admin,moderator')]
public function admin(Request $request): Response
{
    // Only admin or moderator can access
}
```

## Built-in Middleware

Nexus Framework includes several built-in middleware.

### CheckForMaintenanceMode

Redirects users to maintenance page when app is down:

```php
<?php

namespace Nexus\Http\Middleware;

use Closure;
use Nexus\Core\Application;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class CheckForMaintenanceMode implements Middleware
{
    public function __construct(
        protected Application $app
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $maintenanceFile = $this->app->basePath('storage/framework/down');

        if (!file_exists($maintenanceFile)) {
            return $next($request);
        }

        // Check for bypass secret
        if ($this->hasValidSecret($request)) {
            return $next($request);
        }

        return $this->renderMaintenancePage();
    }
}
```

## Common Middleware Examples

### Authentication Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class AuthMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            return Response::redirect('/login');
        }

        // Add user to request
        $request->user = $this->getUser($_SESSION['user_id']);

        return $next($request);
    }

    protected function getUser(int $userId): ?object
    {
        // Fetch user from database
        return User::find($userId);
    }
}
```

### CORS Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class CorsMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers['Access-Control-Allow-Origin'] = '*';
        $response->headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE, OPTIONS';
        $response->headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization';
        $response->headers['Access-Control-Max-Age'] = '86400';

        // Handle preflight requests
        if ($request->method() === 'OPTIONS') {
            return Response::text('', 200);
        }

        return $response;
    }
}
```

### CSRF Protection Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class CsrfMiddleware implements Middleware
{
    protected array $except = [
        '/api/*',  // Exclude API routes
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Only check POST, PUT, DELETE requests
        if (!in_array($request->method(), ['POST', 'PUT', 'DELETE'])) {
            return $next($request);
        }

        // Check if route is excluded
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        // Verify CSRF token
        $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');

        if (!$this->validateToken($token)) {
            return Response::json(['error' => 'CSRF token mismatch'], 419);
        }

        return $next($request);
    }

    protected function validateToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION['_csrf_token'] ?? null;

        return $token && $sessionToken && hash_equals($sessionToken, $token);
    }

    protected function shouldSkip(Request $request): bool
    {
        foreach ($this->except as $pattern) {
            if ($this->matchesPattern($pattern, $request->path())) {
                return true;
            }
        }

        return false;
    }

    protected function matchesPattern(string $pattern, string $path): bool
    {
        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern);

        return (bool) preg_match('#^' . $pattern . '$#', $path);
    }
}
```

### Request Logging Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class LogRequestMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Log request
        $this->logRequest($request);

        $response = $next($request);

        // Log response
        $duration = microtime(true) - $startTime;
        $this->logResponse($request, $response, $duration);

        return $response;
    }

    protected function logRequest(Request $request): void
    {
        $log = sprintf(
            "[%s] %s %s - IP: %s - User-Agent: %s",
            date('Y-m-d H:i:s'),
            $request->method(),
            $request->path(),
            $request->ip(),
            $request->userAgent()
        );

        error_log($log);
    }

    protected function logResponse(Request $request, Response $response, float $duration): void
    {
        $log = sprintf(
            "[%s] %s %s - Status: %d - Duration: %.2fms",
            date('Y-m-d H:i:s'),
            $request->method(),
            $request->path(),
            $response->statusCode,
            $duration * 1000
        );

        error_log($log);
    }
}
```

### API Authentication Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class ApiAuthMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return Response::json([
                'error' => 'API token required'
            ], 401);
        }

        $user = $this->validateToken($token);

        if (!$user) {
            return Response::json([
                'error' => 'Invalid API token'
            ], 401);
        }

        // Add user to request
        $request->user = $user;

        return $next($request);
    }

    protected function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization');

        if (!$header) {
            return null;
        }

        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function validateToken(string $token): ?object
    {
        // Validate token and return user
        $apiToken = ApiToken::where('token', $token)->first();

        if (!$apiToken || $apiToken->isExpired()) {
            return null;
        }

        return $apiToken->user;
    }
}
```

### Rate Limiting Middleware

```php
<?php

namespace App\Middleware;

use Closure;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class ThrottleMiddleware implements Middleware
{
    protected int $maxAttempts = 60;
    protected int $decayMinutes = 1;

    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;

        $key = $this->resolveRequestSignature($request);

        if ($this->tooManyAttempts($key)) {
            return Response::json([
                'error' => 'Too many requests. Please try again later.'
            ], 429);
        }

        $this->hit($key);

        $response = $next($request);

        return $this->addHeaders($response, $key);
    }

    protected function resolveRequestSignature(Request $request): string
    {
        return sha1($request->ip() . '|' . $request->path());
    }

    protected function tooManyAttempts(string $key): bool
    {
        $attempts = $this->getAttempts($key);
        return $attempts >= $this->maxAttempts;
    }

    protected function hit(string $key): void
    {
        $cacheKey = "throttle:{$key}";
        $attempts = (int) ($_SESSION[$cacheKey] ?? 0);

        $_SESSION[$cacheKey] = $attempts + 1;
        $_SESSION[$cacheKey . ':expires'] = time() + ($this->decayMinutes * 60);
    }

    protected function getAttempts(string $key): int
    {
        $cacheKey = "throttle:{$key}";
        $expires = $_SESSION[$cacheKey . ':expires'] ?? 0;

        if (time() > $expires) {
            unset($_SESSION[$cacheKey]);
            return 0;
        }

        return (int) ($_SESSION[$cacheKey] ?? 0);
    }

    protected function addHeaders(Response $response, string $key): Response
    {
        $attempts = $this->getAttempts($key);
        $remaining = max(0, $this->maxAttempts - $attempts);

        $response->headers['X-RateLimit-Limit'] = (string) $this->maxAttempts;
        $response->headers['X-RateLimit-Remaining'] = (string) $remaining;

        return $response;
    }
}
```

## Middleware Pipeline

Multiple middleware execute in order:

```php
#[Get('/admin/users', 'admin.users')]
#[Middleware(['auth', 'verified', 'admin'])]
public function adminUsers(Request $request): Response
{
    // Execution order:
    // 1. AuthMiddleware
    // 2. VerifiedMiddleware
    // 3. AdminMiddleware
    // 4. Controller method
}
```

## Terminating Middleware

Stop request without calling controller:

```php
<?php

namespace App\Middleware;

use Closure;
use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class IpWhitelistMiddleware implements Middleware
{
    protected array $whitelist = [
        '127.0.0.1',
        '192.168.1.1',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->ip(), $this->whitelist)) {
            // Stop here - don't call controller
            return Response::json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
```

## Best Practices

1. **Single Responsibility**: Each middleware should do one thing
2. **Order Matters**: Apply middleware in logical order
3. **Performance**: Keep middleware lightweight
4. **Reusability**: Make middleware generic and reusable
5. **Error Handling**: Handle errors gracefully
6. **Security**: Validate and sanitize data
7. **Testing**: Write tests for middleware logic
8. **Documentation**: Document middleware purpose and usage

## Next Steps

- Learn about [Routing](routing.md)
- Understand [Controllers](controllers.md)
- Explore [Request & Response](request-response.md)
- Work with [Authentication](authentication.md)
