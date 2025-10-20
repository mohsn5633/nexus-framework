# HTTP Client (CURL)

Nexus Framework provides a powerful, fluent HTTP client built on CURL for making HTTP requests to external APIs and services.

## Table of Contents

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [Request Methods](#request-methods)
- [Headers and Authentication](#headers-and-authentication)
- [Request Data](#request-data)
- [Response Handling](#response-handling)
- [File Uploads](#file-uploads)
- [File Downloads](#file-downloads)
- [Retry Logic](#retry-logic)
- [Async Requests](#async-requests)
- [Middleware](#middleware)
- [Examples](#examples)

## Introduction

The HTTP Client provides a clean, expressive API for making HTTP requests with features including:

- **Full HTTP Methods**: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS
- **CURL-Based**: Leverages CURL for performance and reliability
- **Fluent API**: Chainable methods for easy request building
- **Auto Retry**: Automatic retry with exponential backoff
- **Authentication**: Bearer tokens, Basic Auth support
- **File Operations**: Upload and download files
- **JSON Support**: Automatic JSON encoding/decoding
- **Response Handling**: Rich response object with helpers
- **SSL/TLS**: Full SSL/TLS configuration
- **Async Requests**: Non-blocking requests for better performance

## Configuration

### Environment Variables

Configure HTTP client settings in `.env`:

```env
HTTP_TIMEOUT=30
HTTP_CONNECT_TIMEOUT=10
HTTP_VERIFY_SSL=true
HTTP_FOLLOW_REDIRECTS=true
HTTP_MAX_REDIRECTS=10
HTTP_USER_AGENT="Nexus-HTTP-Client/1.0"

# Retry Configuration
HTTP_RETRY_ENABLED=false
HTTP_RETRY_MAX=3
HTTP_RETRY_DELAY=1000

# Proxy (optional)
HTTP_PROXY_ENABLED=false
HTTP_PROXY_HOST=proxy.example.com
HTTP_PROXY_PORT=8080
HTTP_PROXY_USERNAME=user
HTTP_PROXY_PASSWORD=pass
```

### Configuration File

HTTP configuration is in `config/http.php`:

```php
return [
    'default' => [
        'timeout' => env('HTTP_TIMEOUT', 30),
        'verify_ssl' => env('HTTP_VERIFY_SSL', true),
        'follow_redirects' => env('HTTP_FOLLOW_REDIRECTS', true),
    ],

    'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ],

    'retry' => [
        'enabled' => env('HTTP_RETRY_ENABLED', false),
        'max_retries' => env('HTTP_RETRY_MAX', 3),
        'delay' => env('HTTP_RETRY_DELAY', 1000),
    ],
];
```

## Basic Usage

### Creating a Client

```php
use Nexus\Http\Client\HttpClient;

// Simple client
$client = HttpClient::create();

// Client with configuration
$client = HttpClient::create([
    'base_url' => 'https://api.example.com',
    'timeout' => 30,
    'headers' => [
        'Accept' => 'application/json'
    ]
]);

// Using helper
$client = http();
```

### Making Requests

```php
// GET request
$response = $client->get('https://api.example.com/users');

// POST request
$response = $client->post('https://api.example.com/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// With query parameters
$response = $client->get('https://api.example.com/users', [
    'page' => 1,
    'limit' => 10
]);
```

## Request Methods

### GET

```php
$response = $client->get('/users');

// With query parameters
$response = $client->get('/users', ['active' => true]);
```

### POST

```php
$response = $client->post('/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

### PUT

```php
$response = $client->put('/users/1', [
    'name' => 'Jane Doe'
]);
```

### PATCH

```php
$response = $client->patch('/users/1', [
    'email' => 'newemail@example.com'
]);
```

### DELETE

```php
$response = $client->delete('/users/1');
```

### HEAD

```php
$response = $client->head('/users/1');
```

### OPTIONS

```php
$response = $client->options('/api');
```

## Headers and Authentication

### Custom Headers

```php
// Set multiple headers
$client->withHeaders([
    'X-Custom-Header' => 'value',
    'X-API-Version' => '2.0'
]);

// Set single header
$client->withHeader('X-Request-ID', uuid());

// Chaining
$response = $client
    ->withHeader('Accept', 'application/json')
    ->get('/users');
```

### Bearer Token

```php
$client->withToken('your-api-token');

// Custom token type
$client->withToken('token', 'Custom');

// Results in: Authorization: Custom token
```

### Basic Authentication

```php
$client->withBasicAuth('username', 'password');
```

### Base URL

```php
$client->baseUrl('https://api.example.com');

// Now all requests are relative
$response = $client->get('/users'); // https://api.example.com/users
```

## Request Data

### JSON Requests

```php
// Automatic JSON encoding
$response = $client->json('POST', '/users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);

// Or with post()
$client->withHeader('Content-Type', 'application/json');
$response = $client->post('/users', json_encode($data));
```

### Form Data

```php
$response = $client->post('/form', [
    'field1' => 'value1',
    'field2' => 'value2'
]);
```

## Response Handling

### Response Object

```php
$response = $client->get('/users');

// Get response body
$body = $response->body();

// Get as JSON
$data = $response->json();

// Get specific key from JSON
$name = $response->json('name');
$email = $response->json('email', 'default@example.com');

// Get as array
$array = $response->array();

// Get as object
$object = $response->object();

// Get status code
$status = $response->status();

// Get headers
$headers = $response->headers();
$contentType = $response->header('Content-Type');
```

### Status Checks

```php
// Check if successful (2xx)
if ($response->successful()) {
    //
}

// Check if OK (200)
if ($response->ok()) {
    //
}

// Check if redirect (3xx)
if ($response->redirect()) {
    //
}

// Check if client error (4xx)
if ($response->clientError()) {
    //
}

// Check if server error (5xx)
if ($response->serverError()) {
    //
}

// Check if failed
if ($response->failed()) {
    //
}
```

### Throw on Error

```php
// Throw exception if request failed
$response = $client->get('/users')->throw();

// With error handling
try {
    $response = $client->get('/users')->throw();
    $data = $response->json();
} catch (Exception $e) {
    // Handle error
}
```

### Callbacks

```php
$response = $client->get('/users')
    ->onSuccess(function ($response) {
        // Handle success
    })
    ->onError(function ($response) {
        // Handle error
    });
```

## File Uploads

### Upload Files

```php
$response = $client->upload('/files', [
    'title' => 'My Document',
    'category' => 'documents'
], [
    'file' => '/path/to/document.pdf',
    'image' => '/path/to/image.jpg'
]);
```

### Multipart Form

```php
$response = $client->post('/upload', [
    'field' => 'value',
    'file' => new \CURLFile('/path/to/file.pdf', 'application/pdf', 'document.pdf')
]);
```

## File Downloads

### Download File

```php
// Download to file
$success = $client->download(
    'https://example.com/file.pdf',
    '/path/to/save/file.pdf'
);

if ($success) {
    echo "File downloaded successfully";
}
```

### Download to Memory

```php
$response = $client->get('https://example.com/file.pdf');
$content = $response->body();

file_put_contents('/path/to/file.pdf', $content);
```

## Retry Logic

### Configure Retries

```php
// Retry up to 3 times with 1 second delay
$client->retry(3, 1000);

$response = $client->get('/api/endpoint');
```

### With Client Config

```php
$client = HttpClient::create([
    'max_retries' => 3,
    'retry_delay' => 1000
]);
```

## Async Requests

### Single Async Request

```php
$async = $client->async('GET', '/users');

// Set callbacks
$async->then(function ($response) {
    // Handle success
    $users = $response->json();
})->catch(function ($response) {
    // Handle error
});

// Wait for completion
$response = $async->wait();
```

### Multiple Async Requests

```php
use Nexus\Http\Client\AsyncPool;

$pool = new AsyncPool(10); // 10 concurrent requests

$pool->add($client->async('GET', '/users'));
$pool->add($client->async('GET', '/posts'));
$pool->add($client->async('GET', '/comments'));

// Execute all
$responses = $pool->execute();
```

## Middleware

### Add Middleware

```php
$client->middleware(function ($curl, $method, $url, $data) {
    // Modify CURL options
    curl_setopt($curl, CURLOPT_VERBOSE, true);

    // Log request
    error_log("Making {$method} request to {$url}");
});

$response = $client->get('/users');
```

### Multiple Middleware

```php
// Logging middleware
$client->middleware(function ($curl, $method, $url) {
    error_log("[HTTP] {$method} {$url}");
});

// Authentication middleware
$client->middleware(function ($curl) {
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . getToken()
    ]);
});
```

## Examples

### API Client

```php
<?php

class ApiClient
{
    protected HttpClient $client;

    public function __construct(string $apiKey)
    {
        $this->client = HttpClient::create([
            'base_url' => 'https://api.example.com/v1',
            'timeout' => 30
        ]);

        $this->client->withToken($apiKey);
    }

    public function getUsers(int $page = 1): array
    {
        $response = $this->client->get('/users', [
            'page' => $page,
            'per_page' => 20
        ]);

        return $response->json();
    }

    public function createUser(array $data): array
    {
        $response = $this->client
            ->json('POST', '/users', $data)
            ->throw();

        return $response->json();
    }

    public function updateUser(int $id, array $data): array
    {
        $response = $this->client
            ->json('PATCH', "/users/{$id}", $data)
            ->throw();

        return $response->json();
    }

    public function deleteUser(int $id): bool
    {
        $response = $this->client->delete("/users/{$id}");
        return $response->successful();
    }
}

// Usage
$api = new ApiClient('your-api-key');
$users = $api->getUsers();
```

### OAuth2 Integration

```php
<?php

class OAuth2Client
{
    protected HttpClient $client;
    protected ?string $accessToken = null;

    public function __construct(string $baseUrl)
    {
        $this->client = HttpClient::create([
            'base_url' => $baseUrl
        ]);
    }

    public function authenticate(string $clientId, string $clientSecret): void
    {
        $response = $this->client->post('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret
        ]);

        $data = $response->json();
        $this->accessToken = $data['access_token'];
        $this->client->withToken($this->accessToken);
    }

    public function makeRequest(string $method, string $url, array $data = []): mixed
    {
        return $this->client->request($method, $url, $data);
    }
}
```

### Webhook Client

```php
<?php

class WebhookClient
{
    protected HttpClient $client;

    public function __construct()
    {
        $this->client = HttpClient::create([
            'timeout' => 10,
            'max_retries' => 3,
            'retry_delay' => 500
        ]);
    }

    public function send(string $url, array $payload, string $secret): bool
    {
        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        $response = $this->client
            ->withHeaders([
                'X-Webhook-Signature' => $signature,
                'Content-Type' => 'application/json'
            ])
            ->post($url, json_encode($payload));

        return $response->successful();
    }
}

// Usage
$webhook = new WebhookClient();
$webhook->send('https://example.com/webhook', [
    'event' => 'user.created',
    'data' => ['user_id' => 123]
], 'webhook-secret');
```

### GraphQL Client

```php
<?php

class GraphQLClient
{
    protected HttpClient $client;

    public function __construct(string $endpoint, ?string $token = null)
    {
        $this->client = HttpClient::create([
            'base_url' => $endpoint
        ]);

        if ($token) {
            $this->client->withToken($token);
        }
    }

    public function query(string $query, array $variables = []): mixed
    {
        $response = $this->client->json('POST', '', [
            'query' => $query,
            'variables' => $variables
        ])->throw();

        $result = $response->json();

        if (isset($result['errors'])) {
            throw new Exception('GraphQL Error: ' . json_encode($result['errors']));
        }

        return $result['data'];
    }
}

// Usage
$graphql = new GraphQLClient('https://api.example.com/graphql');

$query = <<<'GRAPHQL'
query GetUser($id: ID!) {
    user(id: $id) {
        id
        name
        email
    }
}
GRAPHQL;

$data = $graphql->query($query, ['id' => 123]);
```

### REST API Wrapper

```php
<?php

use Nexus\Http\Client\HttpClient;

class RestApiWrapper
{
    protected HttpClient $client;

    public function __construct(string $baseUrl)
    {
        $this->client = HttpClient::create([
            'base_url' => $baseUrl,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        // Add logging middleware
        $this->client->middleware(function ($curl, $method, $url) {
            error_log("[API] {$method} {$url}");
        });
    }

    public function get(string $endpoint, array $params = []): mixed
    {
        return $this->client->get($endpoint, $params)->json();
    }

    public function post(string $endpoint, array $data): mixed
    {
        return $this->client->json('POST', $endpoint, $data)->json();
    }

    public function put(string $endpoint, array $data): mixed
    {
        return $this->client->json('PUT', $endpoint, $data)->json();
    }

    public function delete(string $endpoint): bool
    {
        return $this->client->delete($endpoint)->successful();
    }
}
```

## Best Practices

1. **Use Base URLs**: Set base URL for consistent API calls
2. **Handle Errors**: Always check response status or use `throw()`
3. **Set Timeouts**: Configure appropriate timeouts for your use case
4. **Retry Logic**: Use retries for transient failures
5. **Authentication**: Store tokens securely, refresh when needed
6. **Logging**: Log requests for debugging and monitoring
7. **SSL Verification**: Keep SSL verification enabled in production
8. **Connection Pooling**: Reuse client instances when possible
9. **Async for Performance**: Use async requests for parallel operations
10. **Error Handling**: Implement proper error handling and fallbacks

## Next Steps

- Learn about [Sockets and WebSockets](sockets.md)
- Explore [Process and Workers](process.md)
- Understand [Queue System](queues.md)
