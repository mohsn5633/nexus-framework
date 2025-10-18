<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class RoutesListCommand extends Command
{
    protected string $signature = 'routes:list';
    protected string $description = 'List all registered routes';

    public function handle(): int
    {
        $this->app->boot();

        $router = $this->app->router();
        $routes = $router->getRoutes();

        if (empty($routes)) {
            $this->warn('No routes registered.');
            return 0;
        }

        $this->info('Registered Routes:');
        $this->line('');

        $allRoutes = [];

        foreach ($routes as $method => $methodRoutes) {
            foreach ($methodRoutes as $path => $route) {
                $allRoutes[] = [
                    'method' => $method,
                    'path' => $path,
                    'name' => $route->getName() ?? '-',
                    'action' => $this->getActionName($route->getAction()),
                ];
            }
        }

        // Sort by path
        usort($allRoutes, fn($a, $b) => strcmp($a['path'], $b['path']));

        // Calculate column widths
        $methodWidth = max(6, ...array_map(fn($r) => strlen($r['method']), $allRoutes));
        $pathWidth = max(4, ...array_map(fn($r) => strlen($r['path']), $allRoutes));
        $nameWidth = max(4, ...array_map(fn($r) => strlen($r['name']), $allRoutes));

        // Print header
        $this->printRow([
            str_pad('METHOD', $methodWidth),
            str_pad('PATH', $pathWidth),
            str_pad('NAME', $nameWidth),
            'ACTION'
        ], true);

        $this->line(str_repeat('-', $methodWidth + $pathWidth + $nameWidth + 50));

        // Print routes
        foreach ($allRoutes as $route) {
            $this->printRow([
                $this->colorMethod($route['method'], $methodWidth),
                str_pad($route['path'], $pathWidth),
                str_pad($route['name'], $nameWidth),
                $route['action']
            ]);
        }

        $this->line('');
        $this->info('Total routes: ' . count($allRoutes));

        return 0;
    }

    protected function getActionName(mixed $action): string
    {
        if (is_string($action)) {
            return $action;
        }

        if (is_array($action)) {
            $class = is_string($action[0]) ? $action[0] : get_class($action[0]);
            return "{$class}@{$action[1]}";
        }

        if ($action instanceof \Closure) {
            return 'Closure';
        }

        return 'Unknown';
    }

    protected function colorMethod(string $method, int $width): string
    {
        $padded = str_pad($method, $width);

        return match ($method) {
            'GET' => "\033[32m{$padded}\033[0m",      // Green
            'POST' => "\033[33m{$padded}\033[0m",     // Yellow
            'PUT' => "\033[34m{$padded}\033[0m",      // Blue
            'PATCH' => "\033[36m{$padded}\033[0m",    // Cyan
            'DELETE' => "\033[31m{$padded}\033[0m",   // Red
            'ANY' => "\033[35m{$padded}\033[0m",      // Magenta
            default => $padded,
        };
    }

    protected function printRow(array $columns, bool $header = false): void
    {
        $row = implode(' │ ', $columns);
        if ($header) {
            $this->line("\033[1m{$row}\033[0m"); // Bold
        } else {
            $this->line($row);
        }
    }
}
