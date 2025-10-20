<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection
    |--------------------------------------------------------------------------
    |
    | This option controls the default queue connection that will be used
    | by the queue system. You may use any of the connections defined
    | in the "connections" array below.
    |
    | Supported: "sync", "database", "redis"
    |
    */

    'default' => env('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Nexus.
    |
    */

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => env('QUEUE_TABLE', 'jobs'),
            'queue' => env('QUEUE_NAME', 'default'),
            'retry_after' => env('QUEUE_RETRY_AFTER', 90),
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => env('REDIS_QUEUE_DB', 0),
            'queue' => env('QUEUE_NAME', 'default'),
            'prefix' => env('REDIS_QUEUE_PREFIX', 'queues:'),
            'retry_after' => env('QUEUE_RETRY_AFTER', 90),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control how and where failed jobs are stored.
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],
];
