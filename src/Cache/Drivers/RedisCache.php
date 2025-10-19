<?php

namespace Nexus\Cache\Drivers;

use Nexus\Cache\CacheDriverInterface;
use Redis;
use RedisException;

class RedisCache implements CacheDriverInterface
{
    protected Redis $redis;
    protected string $prefix;
    protected int $defaultTtl;

    public function __construct(array $config, string $prefix = 'cache')
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('Redis extension is not installed.');
        }

        $this->redis = new Redis();
        $this->prefix = $prefix;
        $this->defaultTtl = config('cache.ttl', 3600);

        try {
            $this->redis->connect(
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? 6379
            );

            if (isset($config['password']) && $config['password']) {
                $this->redis->auth($config['password']);
            }

            if (isset($config['database'])) {
                $this->redis->select($config['database']);
            }
        } catch (RedisException $e) {
            throw new \RuntimeException("Could not connect to Redis: {$e->getMessage()}");
        }
    }

    /**
     * Retrieve an item from the cache by key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($this->getKey($key));

        if ($value === false) {
            return $default;
        }

        return unserialize($value);
    }

    /**
     * Store an item in the cache
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $serialized = serialize($value);

        if ($ttl) {
            return $this->redis->setex($this->getKey($key), $ttl, $serialized);
        }

        return $this->redis->set($this->getKey($key), $serialized);
    }

    /**
     * Store an item in the cache indefinitely
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->redis->set($this->getKey($key), serialize($value));
    }

    /**
     * Determine if an item exists in the cache
     */
    public function has(string $key): bool
    {
        return $this->redis->exists($this->getKey($key)) > 0;
    }

    /**
     * Remove an item from the cache
     */
    public function forget(string $key): bool
    {
        return $this->redis->del($this->getKey($key)) > 0;
    }

    /**
     * Remove all items from the cache
     */
    public function flush(): bool
    {
        return $this->redis->flushDB();
    }

    /**
     * Increment the value of an item in the cache
     */
    public function increment(string $key, int $value = 1): int|bool
    {
        return $this->redis->incrBy($this->getKey($key), $value);
    }

    /**
     * Decrement the value of an item in the cache
     */
    public function decrement(string $key, int $value = 1): int|bool
    {
        return $this->redis->decrBy($this->getKey($key), $value);
    }

    /**
     * Get the prefixed key
     */
    protected function getKey(string $key): string
    {
        return $this->prefix . ':' . $key;
    }

    /**
     * Close the Redis connection
     */
    public function __destruct()
    {
        if ($this->redis) {
            $this->redis->close();
        }
    }
}
