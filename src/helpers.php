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

if (!function_exists('csrf_token')) {
    /**
     * Get the CSRF token
     */
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old(string $key, mixed $default = null): mixed
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['_old_input'][$key] ?? $default;
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
