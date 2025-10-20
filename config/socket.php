<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Socket Type
    |--------------------------------------------------------------------------
    |
    | This option controls the default socket type used throughout the
    | application. Supported: "tcp", "udp", "ssl", "tls"
    |
    */

    'default' => env('SOCKET_TYPE', 'tcp'),

    /*
    |--------------------------------------------------------------------------
    | Socket Timeout
    |--------------------------------------------------------------------------
    |
    | Default timeout in seconds for socket connections and operations.
    |
    */

    'timeout' => env('SOCKET_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | WebSocket Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WebSocket server
    |
    */

    'websocket' => [
        'host' => env('WEBSOCKET_HOST', '0.0.0.0'),
        'port' => env('WEBSOCKET_PORT', 8080),
        'max_clients' => env('WEBSOCKET_MAX_CLIENTS', 100),
        'ping_interval' => env('WEBSOCKET_PING_INTERVAL', 30), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL/TLS Options
    |--------------------------------------------------------------------------
    |
    | Configuration for secure socket connections
    |
    */

    'ssl' => [
        'verify_peer' => env('SOCKET_SSL_VERIFY_PEER', true),
        'verify_peer_name' => env('SOCKET_SSL_VERIFY_PEER_NAME', true),
        'allow_self_signed' => env('SOCKET_SSL_ALLOW_SELF_SIGNED', false),
        'cafile' => env('SOCKET_SSL_CAFILE'),
        'capath' => env('SOCKET_SSL_CAPATH'),
        'local_cert' => env('SOCKET_SSL_LOCAL_CERT'),
        'local_pk' => env('SOCKET_SSL_LOCAL_PK'),
        'passphrase' => env('SOCKET_SSL_PASSPHRASE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Socket Server Configuration
    |--------------------------------------------------------------------------
    |
    | Default configuration for socket servers
    |
    */

    'server' => [
        'host' => env('SOCKET_SERVER_HOST', '0.0.0.0'),
        'port' => env('SOCKET_SERVER_PORT', 8000),
        'backlog' => env('SOCKET_SERVER_BACKLOG', 128),
        'reuse_addr' => env('SOCKET_SERVER_REUSE_ADDR', true),
        'reuse_port' => env('SOCKET_SERVER_REUSE_PORT', true),
    ],
];
