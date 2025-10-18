<?php

namespace Nexus\Core;

use Nexus\Http\Response;
use Nexus\Http\Exceptions\HttpException;
use Throwable;

class ExceptionHandler
{
    public function __construct(
        protected Application $app
    ) {
    }

    /**
     * Handle an exception and return a response
     */
    public function handle(Throwable $e): Response
    {
        $statusCode = $this->getStatusCode($e);
        $debug = $this->app->config()->get('app.debug', false);

        // Try to render error view
        $view = $this->renderErrorView($e, $statusCode, $debug);

        if ($view) {
            return new Response($view, $statusCode);
        }

        // Fallback to simple error response
        return $this->renderFallbackError($e, $statusCode, $debug);
    }

    /**
     * Get the HTTP status code from exception
     */
    protected function getStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        return 500;
    }

    /**
     * Render error view template
     */
    protected function renderErrorView(Throwable $e, int $statusCode, bool $debug): ?string
    {
        // Use debug view when debug mode is enabled
        if ($debug) {
            $debugViewPath = $this->app->basePath("app/Views/errors/debug.php");
            if (file_exists($debugViewPath)) {
                return $this->renderDebugView($e, $statusCode, $debugViewPath);
            }
        }

        // Use status code specific error page
        $viewPath = $this->app->basePath("app/Views/errors/{$statusCode}.php");

        if (!file_exists($viewPath)) {
            return null;
        }

        $data = [
            'statusCode' => $statusCode,
        ];

        // Only include error details if debug mode is enabled
        if ($debug) {
            $data['message'] = $e->getMessage();
            $data['code'] = $e->getCode();
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = $e->getTraceAsString();
            $data['debug'] = true;
        }

        // Add path for 404 errors
        if ($statusCode === 404 && isset($_SERVER['REQUEST_URI'])) {
            $data['path'] = $_SERVER['REQUEST_URI'];
        }

        return $this->renderView($viewPath, $data);
    }

    /**
     * Render debug view with detailed error information
     */
    protected function renderDebugView(Throwable $e, int $statusCode, string $viewPath): string
    {
        $data = [
            'exceptionClass' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'statusCode' => $statusCode,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'fileContent' => $this->getFileContext($e->getFile(), $e->getLine()),
            'stackTrace' => $this->parseStackTrace($e->getTrace()),
            'routes' => $this->getRoutes(),
        ];

        return $this->renderView($viewPath, $data);
    }

    /**
     * Get file context around the error line
     */
    protected function getFileContext(string $file, int $errorLine, int $contextLines = 10): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $start = max(0, $errorLine - $contextLines - 1);
        $end = min(count($lines), $errorLine + $contextLines);

        $context = [];
        for ($i = $start; $i < $end; $i++) {
            $context[$i + 1] = $lines[$i];
        }

        return $context;
    }

    /**
     * Parse stack trace into structured array
     */
    protected function parseStackTrace(array $trace): array
    {
        return array_map(function ($frame) {
            return [
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
                'function' => $frame['function'] ?? null,
                'class' => $frame['class'] ?? null,
                'type' => $frame['type'] ?? null,
                'args' => $frame['args'] ?? [],
            ];
        }, $trace);
    }

    /**
     * Get all registered routes
     */
    protected function getRoutes(): array
    {
        try {
            $router = $this->app->make('router');
            $routesNested = $router->getRoutes();

            $routes = [];
            foreach ($routesNested as $method => $methodRoutes) {
                foreach ($methodRoutes as $path => $routeRegistrar) {
                    $routes[] = [
                        'method' => $routeRegistrar->getMethod(),
                        'path' => $routeRegistrar->getPath(),
                        'name' => $routeRegistrar->getName(),
                        'action' => $this->formatAction($routeRegistrar->getAction()),
                    ];
                }
            }

            return $routes;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Format route action for display
     */
    protected function formatAction($action): string
    {
        if (is_array($action)) {
            $class = is_object($action[0]) ? get_class($action[0]) : $action[0];
            $method = $action[1] ?? 'unknown';
            return "{$class}@{$method}";
        }

        if (is_callable($action)) {
            return 'Closure';
        }

        return (string) $action;
    }

    /**
     * Render a view file with data
     */
    protected function renderView(string $viewFile, array $data): string
    {
        extract($data);

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    /**
     * Render fallback error response
     */
    protected function renderFallbackError(Throwable $e, int $statusCode, bool $debug): Response
    {
        $content = $this->renderFallbackHtml($e, $statusCode, $debug);
        return new Response($content, $statusCode);
    }

    /**
     * Render fallback HTML error page
     */
    protected function renderFallbackHtml(Throwable $e, int $statusCode, bool $debug): string
    {
        $title = $this->getErrorTitle($statusCode);
        $message = $debug ? $e->getMessage() : $this->getErrorMessage($statusCode);

        $html = "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{$statusCode} - {$title}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .error-code {
            font-size: 120px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
        }
        h1 { font-size: 2em; color: #2d3748; margin-bottom: 15px; }
        p { color: #718096; font-size: 1.1em; margin-bottom: 30px; line-height: 1.6; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class=\"error-container\">
        <div class=\"error-code\">{$statusCode}</div>
        <h1>{$title}</h1>
        <p>{$message}</p>";

        if ($debug) {
            $file = htmlspecialchars($e->getFile());
            $line = $e->getLine();
            $trace = htmlspecialchars($e->getTraceAsString());

            $html .= "
        <div style=\"background: #fff5f5; border-left: 4px solid #f56565; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: left;\">
            <h3 style=\"color: #c53030; margin-bottom: 10px;\">Debug Information</h3>
            <p style=\"color: #742a2a; font-size: 0.95em; margin: 0;\">{$file}:{$line}</p>
            <pre style=\"background: #1e1e2e; color: #f8f8f2; padding: 15px; border-radius: 8px; overflow-x: auto; margin-top: 15px; font-size: 0.85em;\">{$trace}</pre>
        </div>";
        }

        $html .= "
        <a href=\"/\" class=\"btn\">Go to Homepage</a>
    </div>
</body>
</html>";

        return $html;
    }

    /**
     * Get error title by status code
     */
    protected function getErrorTitle(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
            default => 'Error',
        };
    }

    /**
     * Get error message by status code
     */
    protected function getErrorMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'The request could not be understood by the server.',
            401 => 'Authentication is required to access this resource.',
            403 => 'You do not have permission to access this resource.',
            404 => 'The requested resource could not be found.',
            405 => 'The request method is not supported for this resource.',
            500 => 'An internal server error occurred.',
            503 => 'The service is temporarily unavailable.',
            default => 'An error occurred while processing your request.',
        };
    }

    /**
     * Log the exception
     */
    public function log(Throwable $e): void
    {
        $logFile = $this->app->basePath('storage/logs/error.log');
        $this->ensureDirectoryExists(dirname($logFile));

        $message = sprintf(
            "[%s] %s: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        error_log($message, 3, $logFile);
    }

    /**
     * Ensure directory exists
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
