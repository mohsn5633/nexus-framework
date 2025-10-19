<?php

namespace Nexus\Cache\Drivers;

use Nexus\Cache\CacheDriverInterface;

class FileCache implements CacheDriverInterface
{
    protected string $path;
    protected string $prefix;
    protected int $defaultTtl;

    public function __construct(array $config, string $prefix = 'cache')
    {
        $this->path = $config['path'];
        $this->prefix = $prefix;
        $this->defaultTtl = config('cache.ttl', 3600);

        // Ensure directory exists
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    /**
     * Retrieve an item from the cache by key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return $default;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            return $default;
        }

        $data = unserialize($contents);

        // Check expiration
        if ($data['expires_at'] !== null && $data['expires_at'] < time()) {
            $this->forget($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Store an item in the cache
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;

        $data = [
            'value' => $value,
            'expires_at' => $ttl ? time() + $ttl : null,
        ];

        $file = $this->getFilePath($key);

        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
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
        $file = $this->getFilePath($key);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * Remove all items from the cache
     */
    public function flush(): bool
    {
        $files = glob($this->path . '/*');

        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

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
     * Get the file path for a cache key
     */
    protected function getFilePath(string $key): string
    {
        $hash = md5($this->prefix . $key);
        return $this->path . '/' . $hash;
    }
}
