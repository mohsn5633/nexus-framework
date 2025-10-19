# Cache

Nexus Framework provides an expressive, unified API for various caching backends. The cache system allows you to store expensive operations results for faster retrieval.

## Table of Contents

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [Cache Drivers](#cache-drivers)
- [Advanced Features](#advanced-features)
- [Practical Examples](#practical-examples)

## Introduction

Caching improves application performance by storing frequently accessed data in fast storage. Nexus supports multiple cache drivers out of the box.

### Features

- **Multiple Drivers**: File, Redis, Array
- **Simple API**: Get, put, forget, flush
- **Remember Pattern**: Cache-or-execute pattern
- **Atomic Operations**: Increment/decrement support
- **TTL Support**: Automatic expiration
- **Prefix Support**: Avoid key collisions

## Configuration

Cache configuration is stored in `config/cache.php`.

### Environment Variables

```env
CACHE_DRIVER=file
CACHE_TTL=3600
CACHE_PREFIX=nexus_cache

# Redis configuration
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_CACHE_DB=1
```

### Configuration File

```php
return [
    'default' => 'file',

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache'),
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'database' => 1,
        ],

        'array' => [
            'driver' => 'array',
        ],
    ],

    'prefix' => 'nexus_cache',
    'ttl' => 3600, // Default TTL in seconds
];
```

## Basic Usage

### Storing Items

```php
use Nexus\Cache\CacheManager;

// Using helper
cache('key', 'value', 3600); // Store for 1 hour

// Using CacheManager
public function index(CacheManager $cache)
{
    // Store for default TTL
    $cache->put('user_count', 150);

    // Store with custom TTL (60 seconds)
    $cache->put('stats', $data, 60);

    // Store forever (until manually deleted)
    $cache->forever('settings', $settings);
}
```

### Retrieving Items

```php
// Get value with default
$count = cache('user_count', 0);

// Using CacheManager
$count = $cache->get('user_count', 0);

// Get without default
$value = $cache->get('key'); // Returns null if not found
```

### Checking Existence

```php
if ($cache->has('user_count')) {
    // Key exists
}
```

### Removing Items

```php
// Remove a single item
$cache->forget('user_count');

// Remove all cached items
$cache->flush();
```

## Cache Drivers

### File Driver (Default)

Stores cache in files on the server.

```php
'file' => [
    'driver' => 'file',
    'path' => storage_path('framework/cache'),
],
```

**Pros**: Simple, no dependencies
**Cons**: Slower than memory-based caches

### Redis Driver

Stores cache in Redis (requires Redis extension).

```php
'redis' => [
    'driver' => 'redis',
    'host' => '127.0.0.1',
    'port' => 6379,
    'password' => null,
    'database' => 1,
],
```

**Installation**:
```bash
# Install Redis extension
pecl install redis

# Or on Ubuntu
sudo apt-get install php-redis
```

**Pros**: Very fast, persistent, supports clustering
**Cons**: Requires Redis server

### Array Driver

Stores cache in memory (testing only).

```php
'array' => [
    'driver' => 'array',
],
```

**Pros**: Instant, perfect for testing
**Cons**: Lost after request

### Using Different Stores

```php
// Use specific store
$cache->store('redis')->put('key', 'value');
$cache->store('file')->get('key');

// Default store
$cache->put('key', 'value');
```

## Advanced Features

### Remember Pattern

Execute and cache in one call:

```php
$users = $cache->remember('active_users', function() {
    return User::where('active', true)->get();
}, 3600);

// Remember forever
$settings = $cache->rememberForever('app_settings', function() {
    return Settings::all();
});
```

### Pull

Get and delete in one operation:

```php
// Get value and remove from cache
$value = $cache->pull('temp_data');
```

### Increment / Decrement

Atomic increment and decrement:

```php
// Increment by 1
$cache->increment('views');

// Increment by custom amount
$cache->increment('points', 10);

// Decrement
$cache->decrement('stock');
$cache->decrement('quantity', 5);
```

### Multiple Items

Work with multiple items at once:

```php
// Get multiple items
$values = $cache->many(['users', 'posts', 'comments']);

// Store multiple items
$cache->putMany([
    'users' => $users,
    'posts' => $posts,
], 3600);
```

## Practical Examples

### Database Query Caching

```php
<?php

namespace App\Controllers;

use Nexus\Cache\CacheManager;
use Nexus\Http\Response;
use App\Models\Post;

class PostController
{
    public function index(CacheManager $cache): Response
    {
        // Cache posts for 1 hour
        $posts = $cache->remember('posts.all', function() {
            return Post::orderBy('created_at', 'DESC')->get();
        }, 3600);

        return Response::json($posts);
    }

    public function show(int $id, CacheManager $cache): Response
    {
        // Cache individual post
        $post = $cache->remember("posts.{$id}", function() use ($id) {
            return Post::find($id);
        }, 3600);

        if (!$post) {
            return Response::json(['error' => 'Not found'], 404);
        }

        return Response::json($post);
    }

    public function update(int $id, CacheManager $cache): Response
    {
        // Update post...

        // Invalidate cache
        $cache->forget("posts.{$id}");
        $cache->forget('posts.all');

        return Response::json(['success' => true]);
    }
}
```

### API Response Caching

```php
<?php

namespace App\Controllers;

use Nexus\Cache\CacheManager;
use Nexus\Http\Response;

class ApiController
{
    public function weather(CacheManager $cache): Response
    {
        // Cache API response for 30 minutes
        $weather = $cache->remember('api.weather', function() {
            // Make external API call
            $response = file_get_contents('https://api.weather.com/current');
            return json_decode($response, true);
        }, 1800);

        return Response::json($weather);
    }
}
```

### Page View Counter

```php
<?php

namespace App\Controllers;

use Nexus\Cache\CacheManager;
use Nexus\Http\Request;
use Nexus\Http\Response;

class PageController
{
    public function show(string $slug, CacheManager $cache): Response
    {
        // Increment view count
        $views = $cache->increment("page.{$slug}.views");

        return Response::view('page', [
            'slug' => $slug,
            'views' => $views
        ]);
    }

    public function stats(CacheManager $cache): Response
    {
        $stats = $cache->many([
            'page.home.views',
            'page.about.views',
            'page.contact.views'
        ]);

        return Response::json($stats);
    }
}
```

### Expensive Computation Caching

```php
<?php

namespace App\Services;

use Nexus\Cache\CacheManager;

class ReportService
{
    public function __construct(
        protected CacheManager $cache
    ) {
    }

    public function generateMonthlyReport(int $month, int $year): array
    {
        $cacheKey = "reports.monthly.{$year}.{$month}";

        return $this->cache->remember($cacheKey, function() use ($month, $year) {
            // Expensive computation
            $sales = $this->calculateSales($month, $year);
            $expenses = $this->calculateExpenses($month, $year);
            $profit = $sales - $expenses;

            return [
                'sales' => $sales,
                'expenses' => $expenses,
                'profit' => $profit,
                'generated_at' => date('Y-m-d H:i:s')
            ];
        }, 86400); // Cache for 24 hours
    }

    public function invalidateReport(int $month, int $year): void
    {
        $cacheKey = "reports.monthly.{$year}.{$month}";
        $this->cache->forget($cacheKey);
    }
}
```

### Session Rate Limiting

```php
<?php

namespace App\Middleware;

use Nexus\Cache\CacheManager;
use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Middleware;

class RateLimitMiddleware extends Middleware
{
    protected int $maxAttempts = 60;
    protected int $decayMinutes = 1;

    public function __construct(
        protected CacheManager $cache
    ) {
    }

    public function handle(Request $request, \Closure $next): Response
    {
        $key = $this->resolveRequestKey($request);

        $attempts = $this->cache->get($key, 0);

        if ($attempts >= $this->maxAttempts) {
            return Response::json([
                'error' => 'Too many requests'
            ], 429);
        }

        $this->cache->put($key, $attempts + 1, $this->decayMinutes * 60);

        return $next($request);
    }

    protected function resolveRequestKey(Request $request): string
    {
        return 'rate_limit:' . $request->ip();
    }
}
```

### Cache Service Class

```php
<?php

namespace App\Services;

use Nexus\Cache\CacheManager;

class CacheService
{
    public function __construct(
        protected CacheManager $cache
    ) {
    }

    /**
     * Cache user data
     */
    public function cacheUser(int $userId, array $data, int $ttl = 3600): void
    {
        $this->cache->put("users.{$userId}", $data, $ttl);
    }

    /**
     * Get cached user
     */
    public function getCachedUser(int $userId): ?array
    {
        return $this->cache->get("users.{$userId}");
    }

    /**
     * Invalidate user cache
     */
    public function invalidateUser(int $userId): void
    {
        $this->cache->forget("users.{$userId}");
    }

    /**
     * Warm cache with popular content
     */
    public function warmCache(): void
    {
        // Cache popular posts
        $popularPosts = Post::orderBy('views', 'DESC')->limit(10)->get();
        $this->cache->put('posts.popular', $popularPosts, 3600);

        // Cache categories
        $categories = Category::all();
        $this->cache->forever('categories.all', $categories);

        // Cache settings
        $settings = Settings::all();
        $this->cache->forever('app.settings', $settings);
    }

    /**
     * Clear all application cache
     */
    public function clearAll(): void
    {
        $this->cache->flush();
    }
}
```

## Cache Tags (Advanced Pattern)

While Nexus doesn't have built-in tag support, you can implement a tag pattern:

```php
class TaggedCache
{
    public function __construct(
        protected CacheManager $cache
    ) {
    }

    public function tags(array $tags): self
    {
        // Store tag relationship
        foreach ($tags as $tag) {
            $keys = $this->cache->get("tag:{$tag}", []);
            // Manage tag keys...
        }

        return $this;
    }

    public function flushTags(array $tags): void
    {
        foreach ($tags as $tag) {
            $keys = $this->cache->get("tag:{$tag}", []);
            foreach ($keys as $key) {
                $this->cache->forget($key);
            }
            $this->cache->forget("tag:{$tag}");
        }
    }
}
```

## Best Practices

1. **Use Appropriate TTL**: Don't cache forever unless truly static
2. **Cache Keys**: Use descriptive, namespaced keys
3. **Invalidation**: Clear cache when data changes
4. **Remember Pattern**: Use for expensive operations
5. **Monitor**: Track cache hit/miss rates
6. **Choose Right Driver**: File for simple, Redis for production
7. **Prefix Keys**: Prevent collisions across applications
8. **Test**: Use array driver for testing

## Cache Commands

Create helpful CLI commands:

```php
<?php

namespace App\Commands;

use Nexus\Console\Command;
use Nexus\Cache\CacheManager;

class CacheClearCommand extends Command
{
    protected string $signature = 'cache:clear';
    protected string $description = 'Clear application cache';

    public function handle(CacheManager $cache): int
    {
        $cache->flush();
        $this->success('Cache cleared successfully!');

        return 0;
    }
}
```

## Performance Tips

1. **Warm Cache**: Pre-populate cache with common queries
2. **Batch Operations**: Use `putMany()` for multiple items
3. **Monitor Expiration**: Set appropriate TTLs
4. **Use Redis**: For high-traffic applications
5. **Cache Selectively**: Don't cache everything
6. **Measure Impact**: Profile before and after caching

## Debugging

```php
// Check if caching is working
$cache->put('test', 'value', 60);
dump($cache->get('test')); // Should output 'value'

// Check cache driver
dump(config('cache.default'));

// Monitor cache operations (in development)
$start = microtime(true);
$data = $cache->remember('key', fn() => expensiveOperation());
$time = microtime(true) - $start;
dump("Cache operation took {$time}s");
```

## Next Steps

- Learn about [Session](session.md)
- Understand [Performance](performance.md)
- Explore [Redis](redis.md)
