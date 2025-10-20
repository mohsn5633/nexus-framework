<?php

namespace Nexus\Http\Middleware;

use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Support\RateLimiter;

/**
 * Rate Limit Middleware
 *
 * Limits the number of requests from an IP or user within a time window
 */
class RateLimitMiddleware extends Middleware
{
    protected RateLimiter $limiter;

    /**
     * Maximum number of requests allowed
     */
    protected int $maxAttempts = 60;

    /**
     * Time window in seconds
     */
    protected int $decaySeconds = 60;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle the incoming request
     *
     * @param Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle(Request $request, \Closure $next): Response
    {
        $key = $this->resolveRequestKey($request);

        if ($this->limiter->tooManyAttempts($key, $this->maxAttempts)) {
            return $this->buildRateLimitResponse($key);
        }

        $this->limiter->hit($key, $this->decaySeconds);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $this->maxAttempts,
            $this->calculateRemainingAttempts($key)
        );
    }

    /**
     * Resolve the rate limit key for the request
     *
     * @param Request $request
     * @return string
     */
    protected function resolveRequestKey(Request $request): string
    {
        // Use IP address as default identifier
        $ip = $request->ip();

        // You can customize this to use user ID for authenticated requests
        // if (auth()->check()) {
        //     return $this->limiter->for(auth()->id(), 'api');
        // }

        return $this->limiter->for($ip, 'global');
    }

    /**
     * Build the rate limit exceeded response
     *
     * @param string $key
     * @return Response
     */
    protected function buildRateLimitResponse(string $key): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        $response = Response::json([
            'error' => 'Too many requests',
            'message' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => $retryAfter
        ], 429);

        return $this->addHeaders(
            $response,
            $this->maxAttempts,
            0,
            $retryAfter
        );
    }

    /**
     * Calculate remaining attempts
     *
     * @param string $key
     * @return int
     */
    protected function calculateRemainingAttempts(string $key): int
    {
        return $this->limiter->remaining($key, $this->maxAttempts);
    }

    /**
     * Add rate limit headers to response
     *
     * @param Response $response
     * @param int $maxAttempts
     * @param int $remainingAttempts
     * @param int|null $retryAfter
     * @return Response
     */
    protected function addHeaders(
        Response $response,
        int $maxAttempts,
        int $remainingAttempts,
        ?int $retryAfter = null
    ): Response {
        $headers = [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remainingAttempts),
        ];

        if ($retryAfter !== null) {
            $headers['Retry-After'] = $retryAfter;
            $headers['X-RateLimit-Reset'] = time() + $retryAfter;
        }

        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }

        return $response;
    }

    /**
     * Set custom rate limit for this middleware instance
     *
     * @param int $maxAttempts
     * @param int $decaySeconds
     * @return self
     */
    public function withRateLimit(int $maxAttempts, int $decaySeconds = 60): self
    {
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;

        return $this;
    }
}
