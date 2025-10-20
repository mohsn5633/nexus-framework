<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Default configuration for HTTP client requests
    |
    */

    'default' => [
        'timeout' => env('HTTP_TIMEOUT', 30),
        'connect_timeout' => env('HTTP_CONNECT_TIMEOUT', 10),
        'verify_ssl' => env('HTTP_VERIFY_SSL', true),
        'follow_redirects' => env('HTTP_FOLLOW_REDIRECTS', true),
        'max_redirects' => env('HTTP_MAX_REDIRECTS', 10),
        'user_agent' => env('HTTP_USER_AGENT', 'Nexus-HTTP-Client/1.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic request retries
    |
    */

    'retry' => [
        'enabled' => env('HTTP_RETRY_ENABLED', false),
        'max_retries' => env('HTTP_RETRY_MAX', 3),
        'delay' => env('HTTP_RETRY_DELAY', 1000), // milliseconds
        'multiplier' => env('HTTP_RETRY_MULTIPLIER', 2), // exponential backoff
    ],

    /*
    |--------------------------------------------------------------------------
    | Base URLs
    |--------------------------------------------------------------------------
    |
    | Named base URLs for different services
    |
    */

    'base_urls' => [
        'api' => env('API_BASE_URL'),
        'cdn' => env('CDN_BASE_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Headers
    |--------------------------------------------------------------------------
    |
    | Default headers to send with all requests
    |
    */

    'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL/TLS Configuration
    |--------------------------------------------------------------------------
    |
    | SSL/TLS options for HTTPS requests
    |
    */

    'ssl' => [
        'verify_peer' => env('HTTP_SSL_VERIFY_PEER', true),
        'verify_host' => env('HTTP_SSL_VERIFY_HOST', 2),
        'cafile' => env('HTTP_SSL_CAFILE'),
        'capath' => env('HTTP_SSL_CAPATH'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Proxy Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP proxy settings
    |
    */

    'proxy' => [
        'enabled' => env('HTTP_PROXY_ENABLED', false),
        'host' => env('HTTP_PROXY_HOST'),
        'port' => env('HTTP_PROXY_PORT'),
        'username' => env('HTTP_PROXY_USERNAME'),
        'password' => env('HTTP_PROXY_PASSWORD'),
        'type' => env('HTTP_PROXY_TYPE', 'http'), // http, https, socks5
    ],

    /*
    |--------------------------------------------------------------------------
    | Cookie Configuration
    |--------------------------------------------------------------------------
    |
    | Cookie handling settings
    |
    */

    'cookies' => [
        'enabled' => env('HTTP_COOKIES_ENABLED', true),
        'file' => storage_path('http/cookies.txt'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Configuration
    |--------------------------------------------------------------------------
    |
    | Debug and logging settings
    |
    */

    'debug' => [
        'enabled' => env('HTTP_DEBUG', false),
        'log_requests' => env('HTTP_LOG_REQUESTS', false),
        'log_responses' => env('HTTP_LOG_RESPONSES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Client-side rate limiting for outgoing requests
    |
    */

    'rate_limit' => [
        'enabled' => env('HTTP_RATE_LIMIT_ENABLED', false),
        'max_requests' => env('HTTP_RATE_LIMIT_MAX', 60),
        'per_seconds' => env('HTTP_RATE_LIMIT_PERIOD', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Pooling
    |--------------------------------------------------------------------------
    |
    | Keep-alive and connection pooling settings
    |
    */

    'pool' => [
        'enabled' => env('HTTP_POOL_ENABLED', true),
        'max_connections' => env('HTTP_POOL_MAX_CONNECTIONS', 10),
        'max_idle_time' => env('HTTP_POOL_MAX_IDLE_TIME', 30),
    ],
];
