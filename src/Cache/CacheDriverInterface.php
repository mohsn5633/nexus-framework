<?php

namespace Nexus\Cache;

interface CacheDriverInterface
{
    /**
     * Retrieve an item from the cache by key
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Store an item in the cache
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Store an item in the cache indefinitely
     */
    public function forever(string $key, mixed $value): bool;

    /**
     * Determine if an item exists in the cache
     */
    public function has(string $key): bool;

    /**
     * Remove an item from the cache
     */
    public function forget(string $key): bool;

    /**
     * Remove all items from the cache
     */
    public function flush(): bool;

    /**
     * Increment the value of an item in the cache
     */
    public function increment(string $key, int $value = 1): int|bool;

    /**
     * Decrement the value of an item in the cache
     */
    public function decrement(string $key, int $value = 1): int|bool;
}
