<?php

namespace Nexus\Support;

use Nexus\Cache\CacheManager;

/**
 * Rate Limiter Service
 *
 * Provides rate limiting functionality using cache drivers
 */
class RateLimiter
{
    protected CacheManager $cache;

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Attempt to acquire a rate limit slot
     *
     * @param string $key Unique identifier for the rate limit
     * @param int $maxAttempts Maximum number of attempts allowed
     * @param int $decaySeconds Time window in seconds
     * @return bool True if attempt is allowed, false if rate limited
     */
    public function attempt(string $key, int $maxAttempts = 60, int $decaySeconds = 60): bool
    {
        $key = $this->resolveKey($key);

        $attempts = $this->attempts($key);

        if ($attempts >= $maxAttempts) {
            return false;
        }

        $this->hit($key, $decaySeconds);

        return true;
    }

    /**
     * Increment the attempt counter
     *
     * @param string $key
     * @param int $decaySeconds
     * @return int Current attempt count
     */
    public function hit(string $key, int $decaySeconds = 60): int
    {
        $key = $this->resolveKey($key);

        $this->cache->increment($key, 1);

        // Set TTL on first hit
        if ($this->attempts($key) === 1) {
            $this->cache->put($key, 1, $decaySeconds);
        }

        return $this->attempts($key);
    }

    /**
     * Get current number of attempts
     *
     * @param string $key
     * @return int
     */
    public function attempts(string $key): int
    {
        $key = $this->resolveKey($key);
        return (int) $this->cache->get($key, 0);
    }

    /**
     * Reset the rate limiter for a key
     *
     * @param string $key
     * @return bool
     */
    public function resetAttempts(string $key): bool
    {
        $key = $this->resolveKey($key);
        return $this->cache->forget($key);
    }

    /**
     * Get remaining attempts
     *
     * @param string $key
     * @param int $maxAttempts
     * @return int
     */
    public function remaining(string $key, int $maxAttempts): int
    {
        $key = $this->resolveKey($key);
        $attempts = $this->attempts($key);

        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get time until rate limit resets
     *
     * @param string $key
     * @return int Seconds until reset (0 if not rate limited)
     */
    public function availableIn(string $key): int
    {
        $key = $this->resolveKey($key);

        // Check if key exists and get TTL
        if ($this->cache->has($key)) {
            // Most cache drivers don't expose TTL, so we'll return a default
            // In production, you might want to store reset time separately
            return 60;
        }

        return 0;
    }

    /**
     * Check if rate limit has been exceeded
     *
     * @param string $key
     * @param int $maxAttempts
     * @return bool
     */
    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        $key = $this->resolveKey($key);
        return $this->attempts($key) >= $maxAttempts;
    }

    /**
     * Clear all rate limit data for a key
     *
     * @param string $key
     * @return bool
     */
    public function clear(string $key): bool
    {
        return $this->resetAttempts($key);
    }

    /**
     * Resolve the full cache key
     *
     * @param string $key
     * @return string
     */
    protected function resolveKey(string $key): string
    {
        return 'rate_limit:' . $key;
    }

    /**
     * Create a rate limiter for a specific user/IP
     *
     * @param string $identifier User ID, IP address, or other identifier
     * @param string $action Action being rate limited (e.g., 'login', 'api', 'email')
     * @return string Cache key
     */
    public function for(string $identifier, string $action = 'default'): string
    {
        return $action . ':' . $identifier;
    }
}
