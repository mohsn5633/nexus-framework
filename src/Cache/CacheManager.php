<?php

namespace Nexus\Cache;

use Nexus\Core\Config;
use Nexus\Cache\Drivers\FileCache;
use Nexus\Cache\Drivers\RedisCache;
use Nexus\Cache\Drivers\ArrayCache;

class CacheManager
{
    protected array $stores = [];
    protected ?string $defaultStore = null;

    public function __construct(
        protected Config $config
    ) {
        $this->defaultStore = $config->get('cache.default', 'file');
    }

    /**
     * Get a cache store instance
     */
    public function store(?string $name = null): CacheDriverInterface
    {
        $name = $name ?? $this->defaultStore;

        if (!isset($this->stores[$name])) {
            $this->stores[$name] = $this->createDriver($name);
        }

        return $this->stores[$name];
    }

    /**
     * Get an item from the cache
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store()->get($key, $default);
    }

    /**
     * Store an item in the cache
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->store()->put($key, $value, $ttl);
    }

    /**
     * Store an item in the cache indefinitely
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->store()->forever($key, $value);
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result
     */
    public function remember(string $key, \Closure $callback, ?int $ttl = null): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever
     */
    public function rememberForever(string $key, \Closure $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->forever($key, $value);

        return $value;
    }

    /**
     * Retrieve an item from the cache and delete it
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);

        return $value;
    }

    /**
     * Determine if an item exists in the cache
     */
    public function has(string $key): bool
    {
        return $this->store()->has($key);
    }

    /**
     * Remove an item from the cache
     */
    public function forget(string $key): bool
    {
        return $this->store()->forget($key);
    }

    /**
     * Remove all items from the cache
     */
    public function flush(): bool
    {
        return $this->store()->flush();
    }

    /**
     * Increment the value of an item in the cache
     */
    public function increment(string $key, int $value = 1): int|bool
    {
        return $this->store()->increment($key, $value);
    }

    /**
     * Decrement the value of an item in the cache
     */
    public function decrement(string $key, int $value = 1): int|bool
    {
        return $this->store()->decrement($key, $value);
    }

    /**
     * Get multiple items from the cache
     */
    public function many(array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }

        return $result;
    }

    /**
     * Store multiple items in the cache
     */
    public function putMany(array $values, ?int $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value, $ttl);
        }

        return true;
    }

    /**
     * Create a cache driver instance
     */
    protected function createDriver(string $name): CacheDriverInterface
    {
        $config = $this->config->get("cache.stores.{$name}");

        if (!$config) {
            throw new \InvalidArgumentException("Cache store [{$name}] is not configured.");
        }

        $driver = $config['driver'] ?? 'file';

        return match ($driver) {
            'file' => new FileCache($config, $this->config->get('cache.prefix')),
            'redis' => new RedisCache($config, $this->config->get('cache.prefix')),
            'array' => new ArrayCache($config, $this->config->get('cache.prefix')),
            default => throw new \InvalidArgumentException("Unsupported cache driver [{$driver}]")
        };
    }

    /**
     * Dynamically pass methods to the default store
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->store()->$method(...$parameters);
    }
}
