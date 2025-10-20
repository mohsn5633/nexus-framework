# Rate Limiting

Rate limiting helps protect your application from abuse by limiting the number of requests from a user or IP address within a time window.

## Table of Contents

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [Middleware](#middleware)
- [Advanced Usage](#advanced-usage)
- [Examples](#examples)

## Introduction

Nexus Framework provides a flexible rate limiting system that uses cache drivers to store request counts. The rate limiter can be used standalone or through middleware.

### Features

- **Flexible time windows**: Configure custom rate limits and decay periods
- **Cache-based storage**: Uses existing cache drivers (File, Redis, Array)
- **Middleware support**: Easy integration with routes
- **Custom keys**: Rate limit by IP, user, API key, or any identifier
- **Response headers**: Automatic X-RateLimit headers
- **Helper functions**: Convenient helper for quick access

## Configuration

Rate limiting uses your configured cache driver. Configure cache in `config/cache.php`:

```env
CACHE_DRIVER=redis
CACHE_TTL=3600
```

For production, Redis is recommended for better performance.

## Basic Usage

### Using the RateLimiter Service

```php
use Nexus\Support\RateLimiter;

class ApiController
{
    public function __construct(
        protected RateLimiter $limiter
    ) {}

    public function endpoint(Request $request): Response
    {
        $key = $this->limiter->for($request->ip(), 'api');

        if (!$this->limiter->attempt($key, 60, 60)) {
            return Response::json([
                'error' => 'Too many requests'
            ], 429);
        }

        // Process request...
        return Response::json(['success' => true]);
    }
}
```

### Using Helper Function

```php
$limiter = rate_limiter();

// Check if attempt is allowed
if ($limiter->tooManyAttempts('user:123:login', 5)) {
    return Response::json(['error' => 'Too many login attempts'], 429);
}

// Record attempt
$limiter->hit('user:123:login', 900); // 15 minutes
```

## Middleware

### Applying to Routes

```php
use Nexus\Http\Middleware\RateLimitMiddleware;

// In your router or controller
#[Get('/api/data', 'api.data')]
#[Middleware(RateLimitMiddleware::class)]
public function getData(): Response
{
    return Response::json(['data' => 'value']);
}
```

### Global Middleware

Register in your application bootstrap:

```php
$router->middleware('throttle', RateLimitMiddleware::class);
```

### Custom Rate Limits

```php
use Nexus\Http\Middleware\RateLimitMiddleware;

class ApiRateLimitMiddleware extends RateLimitMiddleware
{
    protected int $maxAttempts = 100;  // 100 requests
    protected int $decaySeconds = 60;   // per minute
}
```

## Advanced Usage

### Custom Keys

```php
// Rate limit by user ID
$key = $limiter->for(auth()->id(), 'api');

// Rate limit by API key
$key = $limiter->for($request->header('X-API-Key'), 'external-api');

// Rate limit by email
$key = $limiter->for($request->input('email'), 'password-reset');
```

### Check Remaining Attempts

```php
$key = $limiter->for($request->ip(), 'api');

$remaining = $limiter->remaining($key, 60);
$attempts = $limiter->attempts($key);

return Response::json([
    'remaining' => $remaining,
    'total_attempts' => $attempts
]);
```

### Reset Rate Limit

```php
// Clear rate limit for a user
$limiter->resetAttempts('user:123:api');

// Or use clear
$limiter->clear('user:123:api');
```

### Get Time Until Reset

```php
$seconds = $limiter->availableIn('user:123:api');

return Response::json([
    'retry_after' => $seconds,
    'retry_at' => now()->addSeconds($seconds)->toDateTimeString()
]);
```

## Examples

### API Rate Limiting

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Support\RateLimiter;
use Nexus\Http\Route\Get;

class ApiController
{
    public function __construct(
        protected RateLimiter $limiter
    ) {}

    #[Get('/api/users', 'api.users')]
    public function users(Request $request): Response
    {
        // 60 requests per minute per IP
        $key = $this->limiter->for($request->ip(), 'api');

        if ($this->limiter->tooManyAttempts($key, 60)) {
            $retryAfter = $this->limiter->availableIn($key);

            return Response::json([
                'error' => 'Rate limit exceeded',
                'retry_after' => $retryAfter
            ], 429)
            ->header('Retry-After', $retryAfter)
            ->header('X-RateLimit-Limit', 60)
            ->header('X-RateLimit-Remaining', 0);
        }

        $this->limiter->hit($key, 60);

        // Process request
        $users = User::all();

        return Response::json($users)
            ->header('X-RateLimit-Limit', 60)
            ->header('X-RateLimit-Remaining', $this->limiter->remaining($key, 60));
    }
}
```

### Login Rate Limiting

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Support\RateLimiter;
use Nexus\Http\Route\Post;

class AuthController
{
    public function __construct(
        protected RateLimiter $limiter
    ) {}

    #[Post('/login', 'auth.login')]
    public function login(Request $request): Response
    {
        $email = $request->input('email');
        $key = $this->limiter->for($email, 'login');

        // 5 login attempts per 15 minutes
        if ($this->limiter->tooManyAttempts($key, 5)) {
            $seconds = $this->limiter->availableIn($key);

            return Response::json([
                'error' => 'Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.'
            ], 429);
        }

        // Attempt login
        if (!$this->attemptLogin($email, $request->input('password'))) {
            $this->limiter->hit($key, 900); // 15 minutes

            return Response::json([
                'error' => 'Invalid credentials',
                'attempts_remaining' => $this->limiter->remaining($key, 5)
            ], 401);
        }

        // Clear rate limit on successful login
        $this->limiter->clear($key);

        return Response::json([
            'message' => 'Login successful',
            'token' => $this->generateToken()
        ]);
    }
}
```

### Per-User API Rate Limiting

```php
<?php

namespace App\Middleware;

use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Support\RateLimiter;

class ApiUserRateLimitMiddleware extends Middleware
{
    public function __construct(
        protected RateLimiter $limiter
    ) {}

    public function handle(Request $request, \Closure $next): Response
    {
        if (!auth()->check()) {
            return Response::json(['error' => 'Unauthenticated'], 401);
        }

        $userId = auth()->id();
        $key = $this->limiter->for($userId, 'api');

        // Different limits based on user tier
        $maxAttempts = $this->getMaxAttempts($userId);
        $decayMinutes = 1;

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return Response::json([
                'error' => 'API rate limit exceeded',
                'retry_after' => $this->limiter->availableIn($key)
            ], 429);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $response
            ->header('X-RateLimit-Limit', $maxAttempts)
            ->header('X-RateLimit-Remaining', $this->limiter->remaining($key, $maxAttempts));
    }

    protected function getMaxAttempts(int $userId): int
    {
        // Get user's subscription tier
        $user = User::find($userId);

        return match($user->tier) {
            'premium' => 1000,
            'pro' => 500,
            default => 100
        };
    }
}
```

### Dynamic Rate Limiting

```php
class DynamicRateLimitMiddleware extends Middleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        $limiter = rate_limiter();

        // Different limits for different endpoints
        $limits = $this->getRateLimits($request);
        $key = $limiter->for($request->ip(), $limits['name']);

        if ($limiter->tooManyAttempts($key, $limits['max'])) {
            return Response::json([
                'error' => 'Rate limit exceeded for ' . $limits['name']
            ], 429);
        }

        $limiter->hit($key, $limits['decay']);

        return $next($request);
    }

    protected function getRateLimits(Request $request): array
    {
        $path = $request->path();

        return match(true) {
            str_starts_with($path, '/api/search') => [
                'name' => 'search',
                'max' => 10,
                'decay' => 60
            ],
            str_starts_with($path, '/api/export') => [
                'name' => 'export',
                'max' => 5,
                'decay' => 3600
            ],
            default => [
                'name' => 'general',
                'max' => 60,
                'decay' => 60
            ]
        };
    }
}
```

## Best Practices

1. **Use Redis in Production**: File cache is slower for frequent rate limit checks
2. **Clear on Success**: Reset rate limits after successful critical operations
3. **Informative Messages**: Tell users when they can retry
4. **Tier-Based Limits**: Different limits for different user tiers
5. **Monitor Abuse**: Log rate limit violations for security monitoring
6. **Graceful Degradation**: Provide helpful error messages
7. **Document Limits**: Clearly document API rate limits

## Response Headers

The rate limit middleware automatically adds these headers:

- `X-RateLimit-Limit`: Maximum number of requests allowed
- `X-RateLimit-Remaining`: Number of requests remaining
- `X-RateLimit-Reset`: Unix timestamp when the limit resets
- `Retry-After`: Seconds until the user can retry (when rate limited)

## Next Steps

- Learn about [Middleware](middleware.md)
- Understand [Cache](cache.md)
- Explore [Security](security.md)
