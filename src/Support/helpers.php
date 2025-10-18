<?php

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value
        };
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return \Nexus\Core\Application::getInstance()->config()->get($key, $default);
    }
}

if (!function_exists('app')) {
    function app(?string $abstract = null): mixed
    {
        $app = \Nexus\Core\Application::getInstance();

        if (is_null($abstract)) {
            return $app;
        }

        return $app->make($abstract);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return \Nexus\Core\Application::getInstance()->basePath($path);
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('view')) {
    function view(string $name, array $data = []): string
    {
        return app('view')->render($name, $data);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $code = 302): never
    {
        header("Location: $url", true, $code);
        exit;
    }
}

if (!function_exists('json')) {
    function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        die(1);
    }
}

if (!function_exists('dump')) {
    function dump(mixed ...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
    }
}

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): never
    {
        throw match ($code) {
            401 => new \Nexus\Http\Exceptions\UnauthorizedException($message ?: 'Unauthorized'),
            403 => new \Nexus\Http\Exceptions\ForbiddenException($message ?: 'Forbidden'),
            404 => new \Nexus\Http\Exceptions\NotFoundException($message ?: 'Not Found'),
            503 => new \Nexus\Http\Exceptions\ServiceUnavailableException($message ?: 'Service Unavailable'),
            default => new \Nexus\Http\Exceptions\HttpException($code, $message ?: 'Error'),
        };
    }
}

if (!function_exists('abort_if')) {
    function abort_if(bool $condition, int $code, string $message = ''): void
    {
        if ($condition) {
            abort($code, $message);
        }
    }
}

if (!function_exists('abort_unless')) {
    function abort_unless(bool $condition, int $code, string $message = ''): void
    {
        if (!$condition) {
            abort($code, $message);
        }
    }
}
