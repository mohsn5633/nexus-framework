<?php

namespace Nexus\Http;

class Request
{
    protected array $routeParams = [];

    public function __construct(
        protected array $query = [],
        protected array $body = [],
        protected array $server = [],
        protected array $files = [],
        protected array $cookies = []
    ) {
    }

    /**
     * Create a request from globals
     */
    public static function capture(): self
    {
        return new self(
            $_GET,
            $_POST,
            $_SERVER,
            $_FILES,
            $_COOKIE
        );
    }

    /**
     * Get the request method
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Get the request path
     */
    public function path(): string
    {
    
        $path = $this->server['REQUEST_URI'] ?? '/';

        // Remove query string
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }

        return $path;
    }

    /**
     * Get the request URL
     */
    public function url(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    /**
     * Get the full URL
     */
    public function fullUrl(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $this->url();
    }

    /**
     * Check if the request is secure
     */
    public function isSecure(): bool
    {
        return !empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off';
    }

    /**
     * Get a query parameter
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    /**
     * Get an input parameter (from body or query)
     */
    public function input(?string $key = null, mixed $default = null): mixed
    {
        // Parse JSON body if content type is JSON
        if (empty($this->body) && $this->isJson()) {
            $this->body = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        if (is_null($key)) {
            return array_merge($this->query, $this->body);
        }

        // Check body first, then query (body takes precedence)
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Get all input (query + body)
     */
    public function all(): array
    {
        return array_merge($this->query(), $this->input());
    }

    /**
     * Check if input has a key
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    /**
     * Check if input has any of the given keys
     */
    public function hasAny(array $keys): bool
    {
        $input = $this->all();
        foreach ($keys as $key) {
            if (array_key_exists($key, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the request is JSON
     */
    public function isJson(): bool
    {
        return str_contains($this->server['CONTENT_TYPE'] ?? '', 'application/json');
    }

    /**
     * Get a route parameter
     */
    public function route(?string $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->routeParams;
        }

        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Set route parameters
     */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    /**
     * Get a header value
     */
    public function header(string $key, mixed $default = null): mixed
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? $default;
    }

    /**
     * Get the IP address
     */
    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Check if the request has a file
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Get an uploaded file
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get a cookie value
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Magic method to get input as property
     */
    public function __get(string $key): mixed
    {
        return $this->input($key);
    }

    /**
     * Magic method to check if input exists
     */
    public function __isset(string $key): bool
    {
        return $this->input($key) !== null;
    }
}
