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
