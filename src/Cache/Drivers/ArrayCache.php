<?php

namespace Nexus\Cache\Drivers;

use Nexus\Cache\CacheDriverInterface;

class ArrayCache implements CacheDriverInterface
{
    protected array $storage = [];
    protected string $prefix;

    public function __construct(array $config = [], string $prefix = 'cache')
    {
        $this->prefix = $prefix;
    }

    /**
     * Retrieve an item from the cache by key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->getKey($key);

        if (!isset($this->storage[$key])) {
            return $default;
        }

        $item = $this->storage[$key];

        // Check expiration
        if ($item['expires_at'] !== null && $item['expires_at'] < time()) {
            unset($this->storage[$key]);
            return $default;
        }

        return $item['value'];
    }

    /**
     * Store an item in the cache
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $key = $this->getKey($key);

        $this->storage[$key] = [
            'value' => $value,
            'expires_at' => $ttl ? time() + $ttl : null,
        ];

        return true;
    }

    /**
     * Store an item in the cache indefinitely
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value, null);
    }

    /**
     * Determine if an item exists in the cache
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Remove an item from the cache
     */
    public function forget(string $key): bool
    {
        $key = $this->getKey($key);
        unset($this->storage[$key]);
        return true;
    }

    /**
     * Remove all items from the cache
     */
    public function flush(): bool
    {
        $this->storage = [];
        return true;
    }

    /**
     * Increment the value of an item in the cache
     */
    public function increment(string $key, int $value = 1): int|bool
    {
        $current = $this->get($key, 0);

        if (!is_numeric($current)) {
            return false;
        }

        $new = $current + $value;
        $this->put($key, $new);

        return $new;
    }

    /**
     * Decrement the value of an item in the cache
     */
    public function decrement(string $key, int $value = 1): int|bool
    {
        return $this->increment($key, -$value);
    }

    /**
     * Get the prefixed key
     */
    protected function getKey(string $key): string
    {
        return $this->prefix . ':' . $key;
    }

    /**
     * Get all cached items (for testing)
     */
    public function all(): array
    {
        return $this->storage;
    }
}
