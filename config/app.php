<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */

    'name' => env('APP_NAME', 'Nexus Framework'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Protocol
    |--------------------------------------------------------------------------
    |
    | This value determines which protocol your application should use.
    |
    | Supported: "auto", "http", "https"
    | - auto: No enforcement, use whatever protocol the request comes with
    | - http: Force HTTP (useful for local development)
    | - https: Force HTTPS (recommended for production)
    |
    */

    'protocol' => env('APP_PROTOCOL', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale
    |--------------------------------------------------------------------------
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [
        App\Providers\AppServiceProvider::class,
        App\Providers\DatabaseServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        // Session & Cache
        Nexus\Session\SessionServiceProvider::class,
        Nexus\Cache\CacheServiceProvider::class,
    ],
];
