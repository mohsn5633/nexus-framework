<?php

namespace Nexus\Http\Client;

use Exception;

/**
 * HTTP Client
 *
 * A powerful CURL-based HTTP client
 */
class HttpClient
{
    protected array $config = [];
    protected array $headers = [];
    protected array $options = [];
    protected int $timeout = 30;
    protected ?string $baseUrl = null;
    protected array $middleware = [];
    protected int $maxRetries = 0;
    protected int $retryDelay = 1000; // milliseconds

    /**
     * Create a new HTTP client
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->baseUrl = $config['base_url'] ?? null;
        $this->timeout = $config['timeout'] ?? 30;
        $this->headers = $config['headers'] ?? [];
        $this->maxRetries = $config['max_retries'] ?? 0;
        $this->retryDelay = $config['retry_delay'] ?? 1000;
    }

    /**
     * Create a new client instance
     */
    public static function create(array $config = []): self
    {
        return new self($config);
    }

    /**
     * Set base URL
     */
    public function baseUrl(string $url): self
    {
        $this->baseUrl = rtrim($url, '/');
        return $this;
    }

    /**
     * Set request timeout
     */
    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Set request headers
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set a single header
     */
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set authorization bearer token
     */
    public function withToken(string $token, string $type = 'Bearer'): self
    {
        $this->headers['Authorization'] = "{$type} {$token}";
        return $this;
    }

    /**
     * Set basic authentication
     */
    public function withBasicAuth(string $username, string $password): self
    {
        $this->options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        $this->options[CURLOPT_USERPWD] = "{$username}:{$password}";
        return $this;
    }

    /**
     * Set max retries
     */
    public function retry(int $maxRetries, int $delayMs = 1000): self
    {
        $this->maxRetries = $maxRetries;
        $this->retryDelay = $delayMs;
        return $this;
    }

    /**
     * Add middleware
     */
    public function middleware(callable $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Send GET request
     */
    public function get(string $url, array $query = []): Response
    {
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $this->request('GET', $url);
    }

    /**
     * Send POST request
     */
    public function post(string $url, array|string $data = []): Response
    {
        return $this->request('POST', $url, $data);
    }

    /**
     * Send PUT request
     */
    public function put(string $url, array|string $data = []): Response
    {
        return $this->request('PUT', $url, $data);
    }

    /**
     * Send PATCH request
     */
    public function patch(string $url, array|string $data = []): Response
    {
        return $this->request('PATCH', $url, $data);
    }

    /**
     * Send DELETE request
     */
    public function delete(string $url, array $data = []): Response
    {
        return $this->request('DELETE', $url, $data);
    }

    /**
     * Send HEAD request
     */
    public function head(string $url): Response
    {
        return $this->request('HEAD', $url);
    }

    /**
     * Send OPTIONS request
     */
    public function options(string $url): Response
    {
        return $this->request('OPTIONS', $url);
    }

    /**
     * Send request with JSON data
     */
    public function json(string $method, string $url, array $data = []): Response
    {
        $this->withHeader('Content-Type', 'application/json');
        return $this->request($method, $url, json_encode($data));
    }

    /**
     * Send multipart form request with file uploads
     */
    public function upload(string $url, array $data, array $files = []): Response
    {
        $multipart = [];

        foreach ($data as $name => $value) {
            $multipart[$name] = $value;
        }

        foreach ($files as $name => $path) {
            if (file_exists($path)) {
                $multipart[$name] = new \CURLFile($path);
            }
        }

        return $this->request('POST', $url, $multipart);
    }

    /**
     * Send async request (non-blocking)
     */
    public function async(string $method, string $url, array|string $data = []): AsyncRequest
    {
        return new AsyncRequest($this, $method, $url, $data);
    }

    /**
     * Send HTTP request
     */
    public function request(string $method, string $url, array|string $data = []): Response
    {
        $url = $this->buildUrl($url);
        $attempts = 0;
        $lastException = null;

        while ($attempts <= $this->maxRetries) {
            try {
                return $this->executeRequest($method, $url, $data);
            } catch (Exception $e) {
                $lastException = $e;
                $attempts++;

                if ($attempts <= $this->maxRetries) {
                    usleep($this->retryDelay * 1000);
                }
            }
        }

        throw $lastException ?? new Exception("Request failed");
    }

    /**
     * Execute the actual request
     */
    protected function executeRequest(string $method, string $url, array|string $data = []): Response
    {
        $ch = curl_init();

        // Set URL
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set method and data
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

        if (!empty($data) && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (is_array($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }

        // Set headers
        $headers = [];
        foreach ($this->headers as $name => $value) {
            $headers[] = "{$name}: {$value}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->config['verify_ssl'] ?? true);

        // Apply custom options
        foreach ($this->options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        // Apply middleware
        foreach ($this->middleware as $middleware) {
            $middleware($ch, $method, $url, $data);
        }

        // Execute request
        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new Exception("CURL error: {$error} (code: {$errno})");
        }

        // Get response info
        $info = curl_getinfo($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        curl_close($ch);

        // Parse response
        $headerString = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        return new Response($body, $info['http_code'], $this->parseHeaders($headerString), $info);
    }

    /**
     * Build full URL
     */
    protected function buildUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if ($this->baseUrl) {
            return $this->baseUrl . '/' . ltrim($url, '/');
        }

        return $url;
    }

    /**
     * Parse response headers
     */
    protected function parseHeaders(string $headerString): array
    {
        $headers = [];
        $lines = explode("\r\n", $headerString);

        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$name, $value] = explode(':', $line, 2);
                $headers[trim($name)] = trim($value);
            }
        }

        return $headers;
    }

    /**
     * Download file
     */
    public function download(string $url, string $destination): bool
    {
        $ch = curl_init($url);
        $fp = fopen($destination, 'wb');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($ch);

        curl_close($ch);
        fclose($fp);

        return $result !== false;
    }
}
