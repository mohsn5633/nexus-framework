<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Working Directory
    |--------------------------------------------------------------------------
    |
    | The default working directory for spawned processes.
    |
    */

    'working_directory' => env('PROCESS_WORKING_DIR', base_path()),

    /*
    |--------------------------------------------------------------------------
    | Process Timeout
    |--------------------------------------------------------------------------
    |
    | Default timeout in seconds for process execution.
    |
    */

    'timeout' => env('PROCESS_TIMEOUT', 300),

    /*
    |--------------------------------------------------------------------------
    | Worker Pool Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for parallel processing worker pools
    |
    */

    'pool' => [
        'max_workers' => env('WORKER_POOL_MAX_WORKERS', 4),
        'timeout' => env('WORKER_POOL_TIMEOUT', 300),
        'idle_timeout' => env('WORKER_POOL_IDLE_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Variables
    |--------------------------------------------------------------------------
    |
    | Default environment variables to pass to child processes.
    | Set to null to inherit all environment variables from parent.
    |
    */

    'env' => null,

    /*
    |--------------------------------------------------------------------------
    | Signal Handling
    |--------------------------------------------------------------------------
    |
    | Configuration for process signal handling (Unix/Linux only)
    |
    */

    'signals' => [
        'term' => defined('SIGTERM') ? SIGTERM : 15,  // Termination signal
        'kill' => defined('SIGKILL') ? SIGKILL : 9,   // Kill signal
        'int' => defined('SIGINT') ? SIGINT : 2,      // Interrupt signal
    ],

    /*
    |--------------------------------------------------------------------------
    | Process Limits
    |--------------------------------------------------------------------------
    |
    | Resource limits for spawned processes (Unix only)
    |
    */

    'limits' => [
        'memory' => env('PROCESS_MEMORY_LIMIT', '256M'),
        'time' => env('PROCESS_TIME_LIMIT', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | PHP Binary Path
    |--------------------------------------------------------------------------
    |
    | Path to PHP binary for spawning PHP processes.
    | Leave null to use PHP_BINARY constant.
    |
    */

    'php_binary' => env('PHP_BINARY_PATH', PHP_BINARY),
];
