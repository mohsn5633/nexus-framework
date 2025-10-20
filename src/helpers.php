<?php

use Nexus\Core\Application;
use Nexus\Validation\Validator;

if (!function_exists('app')) {
    /**
     * Get the application instance
     */
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        $app = Application::getInstance();

        if (is_null($abstract)) {
            return $app;
        }

        return $app->make($abstract, $parameters);
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config(?string $key = null, mixed $default = null): mixed
    {
        $config = app('config');

        if (is_null($key)) {
            return $config;
        }

        return $config->get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if ($value === 'null') {
            return null;
        }

        return $value;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the base path
     */
    function base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }
}

if (!function_exists('validate')) {
    /**
     * Validate the given data
     */
    function validate(array $data, array $rules, array $messages = []): array
    {
        $validator = new Validator($data, $rules, $messages);
        return $validator->validate();
    }
}

if (!function_exists('view')) {
    /**
     * Render a view
     */
    function view(string $view, array $data = []): string
    {
        return \Nexus\View\View::make($view, $data);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities
     */
    function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('session')) {
    /**
     * Get / put session value
     */
    function session(?string $key = null, mixed $default = null): mixed
    {
        $session = app(\Nexus\Session\SessionManager::class);

        if (is_null($key)) {
            return $session;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $session->put($k, $v);
            }
            return null;
        }

        return $session->get($key, $default);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the CSRF token
     */
    function csrf_token(): string
    {
        return session()->token();
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old(string $key, mixed $default = null): mixed
    {
        return session()->get('_old_input.' . $key, $default);
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public folder
     */
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the path to the storage folder
     */
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset URL
     */
    function asset(string $path): string
    {
        $baseUrl = rtrim(config('app.url', ''), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('storage')) {
    /**
     * Get a storage disk instance
     */
    function storage(?string $disk = null): \Nexus\Storage\Storage
    {
        return \Nexus\Storage\Storage::disk($disk);
    }
}

if (!function_exists('cache')) {
    /**
     * Get / set cache value
     */
    function cache(?string $key = null, mixed $value = null, ?int $ttl = null): mixed
    {
        $cache = app(\Nexus\Cache\CacheManager::class);

        if (is_null($key)) {
            return $cache;
        }

        if (!is_null($value)) {
            return $cache->put($key, $value, $ttl);
        }

        return $cache->get($key);
    }
}

if (!function_exists('auth')) {
    /**
     * Get the auth manager or guard
     */
    function auth(?string $guard = null): mixed
    {
        $auth = app(\Packages\Authentication\Services\AuthManager::class);

        if ($guard) {
            return $auth->guard($guard);
        }

        return $auth;
    }
}

if (!function_exists('rate_limiter')) {
    /**
     * Get the rate limiter instance
     */
    function rate_limiter(): \Nexus\Support\RateLimiter
    {
        return app(\Nexus\Support\RateLimiter::class);
    }
}

if (!function_exists('encrypt')) {
    /**
     * Encrypt a value
     */
    function encrypt(mixed $value, bool $serialize = true): string
    {
        return app(\Nexus\Security\Encrypter::class)->encrypt($value, $serialize);
    }
}

if (!function_exists('decrypt')) {
    /**
     * Decrypt a value
     */
    function decrypt(string $payload, bool $unserialize = true): mixed
    {
        return app(\Nexus\Security\Encrypter::class)->decrypt($payload, $unserialize);
    }
}

if (!function_exists('bcrypt')) {
    /**
     * Hash a value using bcrypt
     */
    function bcrypt(string $value, array $options = []): string
    {
        return \Nexus\Security\Encrypter::hash($value, $options);
    }
}

if (!function_exists('now')) {
    /**
     * Create a new Date instance for the current date and time
     */
    function now(\DateTimeZone|string|null $timezone = null): \Nexus\Support\Date
    {
        return \Nexus\Support\Date::now($timezone);
    }
}

if (!function_exists('today')) {
    /**
     * Create a new Date instance for today
     */
    function today(\DateTimeZone|string|null $timezone = null): \Nexus\Support\Date
    {
        return \Nexus\Support\Date::today($timezone);
    }
}

if (!function_exists('carbon')) {
    /**
     * Parse a string into a Date instance (alias for Date::parse)
     */
    function carbon(string $time, \DateTimeZone|string|null $timezone = null): \Nexus\Support\Date
    {
        return \Nexus\Support\Date::parse($time, $timezone);
    }
}

if (!function_exists('dispatch')) {
    /**
     * Dispatch a job to the queue
     */
    function dispatch(string $job, array $data = [], ?string $queue = null): mixed
    {
        return app(\Nexus\Queue\QueueManager::class)->push($job, $data, $queue);
    }
}

if (!function_exists('dispatch_after')) {
    /**
     * Dispatch a job to the queue after a delay
     */
    function dispatch_after(int $delay, string $job, array $data = [], ?string $queue = null): mixed
    {
        return app(\Nexus\Queue\QueueManager::class)->later($delay, $job, $data, $queue);
    }
}

if (!function_exists('queue')) {
    /**
     * Get the queue manager instance
     */
    function queue(?string $connection = null): \Nexus\Queue\QueueManager|\Nexus\Queue\QueueInterface
    {
        $manager = app(\Nexus\Queue\QueueManager::class);

        if ($connection) {
            return $manager->connection($connection);
        }

        return $manager;
    }
}

if (!function_exists('mail')) {
    /**
     * Get the mail manager instance or send a mailable
     */
    function mail(\Nexus\Mail\Mailable|null $mailable = null): \Nexus\Mail\MailManager|bool
    {
        $manager = app(\Nexus\Mail\MailManager::class);

        if ($mailable) {
            return $manager->send($mailable);
        }

        return $manager;
    }
}

if (!function_exists('http')) {
    /**
     * Create a new HTTP client instance
     */
    function http(array $config = []): \Nexus\Http\Client\HttpClient
    {
        return new \Nexus\Http\Client\HttpClient($config);
    }
}

if (!function_exists('socket')) {
    /**
     * Create a new socket instance
     */
    function socket(string $type = \Nexus\Socket\Socket::TYPE_TCP, array $options = []): \Nexus\Socket\Socket
    {
        return new \Nexus\Socket\Socket($type, $options);
    }
}

if (!function_exists('websocket')) {
    /**
     * Create a new WebSocket server
     */
    function websocket(string $host = '0.0.0.0', int $port = 8080): \Nexus\Socket\WebSocket
    {
        return new \Nexus\Socket\WebSocket($host, $port);
    }
}

if (!function_exists('process')) {
    /**
     * Create a new process
     */
    function process(?string $command = null, ?string $cwd = null, array $env = []): \Nexus\Process\Process
    {
        return new \Nexus\Process\Process($command, $cwd, $env);
    }
}

if (!function_exists('parallel')) {
    /**
     * Execute tasks in parallel
     */
    function parallel(array $tasks): array
    {
        return \Nexus\Process\ProcessPool::parallel($tasks);
    }
}

if (!function_exists('worker_pool')) {
    /**
     * Create a new process pool
     */
    function worker_pool(int $maxWorkers = 4, int $timeout = 300): \Nexus\Process\ProcessPool
    {
        return new \Nexus\Process\ProcessPool($maxWorkers, $timeout);
    }
}
