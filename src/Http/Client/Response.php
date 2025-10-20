<?php

namespace Nexus\Http\Client;

use Exception;

/**
 * HTTP Response
 *
 * Represents an HTTP response
 */
class Response
{
    protected string $body;
    protected int $statusCode;
    protected array $headers;
    protected array $info;

    /**
     * Create a new response
     */
    public function __construct(string $body, int $statusCode, array $headers = [], array $info = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->info = $info;
    }

    /**
     * Get response body
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * Get response as JSON
     */
    public function json(?string $key = null, mixed $default = null): mixed
    {
        $data = json_decode($this->body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to decode JSON: " . json_last_error_msg());
        }

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    /**
     * Get response as array
     */
    public function array(): array
    {
        return $this->json();
    }

    /**
     * Get response as object
     */
    public function object(): object
    {
        return json_decode($this->body);
    }

    /**
     * Get response as XML
     */
    public function xml(): \SimpleXMLElement|false
    {
        return simplexml_load_string($this->body);
    }

    /**
     * Get status code
     */
    public function status(): int
    {
        return $this->statusCode;
    }

    /**
     * Check if response is successful (2xx)
     */
    public function successful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Check if response is OK (200)
     */
    public function ok(): bool
    {
        return $this->statusCode === 200;
    }

    /**
     * Check if response is redirect (3xx)
     */
    public function redirect(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Check if response is client error (4xx)
     */
    public function clientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Check if response is server error (5xx)
     */
    public function serverError(): bool
    {
        return $this->statusCode >= 500;
    }

    /**
     * Check if response failed
     */
    public function failed(): bool
    {
        return !$this->successful();
    }

    /**
     * Get response headers
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get a specific header
     */
    public function header(string $name, mixed $default = null): mixed
    {
        return $this->headers[$name] ?? $default;
    }

    /**
     * Check if header exists
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * Get request info
     */
    public function info(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->info;
        }

        return $this->info[$key] ?? null;
    }

    /**
     * Get content type
     */
    public function contentType(): ?string
    {
        return $this->header('Content-Type');
    }

    /**
     * Get request URL
     */
    public function url(): ?string
    {
        return $this->info['url'] ?? null;
    }

    /**
     * Get total time
     */
    public function totalTime(): ?float
    {
        return $this->info['total_time'] ?? null;
    }

    /**
     * Get download size
     */
    public function downloadSize(): ?int
    {
        return $this->info['download_content_length'] ?? null;
    }

    /**
     * Get upload size
     */
    public function uploadSize(): ?int
    {
        return $this->info['upload_content_length'] ?? null;
    }

    /**
     * Throw exception if response failed
     */
    public function throw(): self
    {
        if ($this->failed()) {
            throw new Exception(
                "HTTP request failed with status {$this->statusCode}: {$this->body}"
            );
        }

        return $this;
    }

    /**
     * Execute callback if successful
     */
    public function onSuccess(callable $callback): self
    {
        if ($this->successful()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Execute callback if failed
     */
    public function onError(callable $callback): self
    {
        if ($this->failed()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Convert response to string
     */
    public function __toString(): string
    {
        return $this->body;
    }

    /**
     * Get response property dynamically
     */
    public function __get(string $name): mixed
    {
        if ($name === 'body') {
            return $this->body();
        }

        if ($name === 'status') {
            return $this->status();
        }

        if ($name === 'headers') {
            return $this->headers();
        }

        return null;
    }
}
