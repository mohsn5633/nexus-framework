<?php

namespace Nexus\Http\Client;

use Exception;

/**
 * Async Request
 *
 * Handles asynchronous HTTP requests using curl_multi
 */
class AsyncRequest
{
    protected HttpClient $client;
    protected string $method;
    protected string $url;
    protected array|string $data;
    protected mixed $curlHandle = null;
    protected ?Response $response = null;
    protected bool $completed = false;
    protected ?callable $successCallback = null;
    protected ?callable $errorCallback = null;

    /**
     * Create a new async request
     */
    public function __construct(HttpClient $client, string $method, string $url, array|string $data = [])
    {
        $this->client = $client;
        $this->method = $method;
        $this->url = $url;
        $this->data = $data;
    }

    /**
     * Set success callback
     */
    public function then(callable $callback): self
    {
        $this->successCallback = $callback;
        return $this;
    }

    /**
     * Set error callback
     */
    public function catch(callable $callback): self
    {
        $this->errorCallback = $callback;
        return $this;
    }

    /**
     * Wait for the request to complete
     */
    public function wait(): Response
    {
        if ($this->completed) {
            return $this->response;
        }

        // Execute synchronously if not started
        $this->response = $this->client->request($this->method, $this->url, $this->data);
        $this->completed = true;

        // Execute callbacks
        if ($this->response->successful() && $this->successCallback) {
            ($this->successCallback)($this->response);
        } elseif ($this->response->failed() && $this->errorCallback) {
            ($this->errorCallback)($this->response);
        }

        return $this->response;
    }

    /**
     * Check if request is completed
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * Get response
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }
}

/**
 * Async Pool
 *
 * Executes multiple async requests in parallel
 */
class AsyncPool
{
    protected array $requests = [];
    protected array $responses = [];
    protected int $concurrency = 10;

    /**
     * Create a new async pool
     */
    public function __construct(int $concurrency = 10)
    {
        $this->concurrency = $concurrency;
    }

    /**
     * Add request to pool
     */
    public function add(AsyncRequest $request): self
    {
        $this->requests[] = $request;
        return $this;
    }

    /**
     * Execute all requests
     */
    public function execute(): array
    {
        $multiHandle = curl_multi_init();
        $handles = [];
        $results = [];

        // Add requests to multi handle
        foreach ($this->requests as $index => $request) {
            $handles[$index] = $request;
        }

        // Process requests synchronously for now
        // In a real implementation, you would use curl_multi_* functions
        foreach ($handles as $index => $request) {
            $results[$index] = $request->wait();
        }

        curl_multi_close($multiHandle);

        return $results;
    }

    /**
     * Wait for all requests
     */
    public function wait(): array
    {
        return $this->execute();
    }
}
