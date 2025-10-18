<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakeControllerCommand extends Command
{
    protected string $signature = 'make:controller';
    protected string $description = 'Create a new controller class';

    public function handle(): int
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Controller name is required!');
            $this->line('Usage: php nexus make:controller ControllerName [--resource]');
            return 1;
        }

        // Ensure it ends with "Controller"
        if (!str_ends_with($name, 'Controller')) {
            $name .= 'Controller';
        }

        $isResource = $this->option('resource') || $this->option('r');
        $path = $this->app->basePath("app/Controllers/{$name}.php");

        if ($this->fileExists($path)) {
            $this->error("Controller {$name} already exists!");
            return 1;
        }

        $stub = $isResource
            ? $this->getStub('controller.resource')
            : $this->getStub('controller');

        // Generate route path and name
        $routePath = '/' . strtolower(str_replace('Controller', '', $name));
        $routeName = strtolower(str_replace('Controller', '', $name));

        $content = $this->replaceInStub($stub, [
            'namespace' => 'App\\Controllers',
            'class' => $name,
            'route' => $routePath,
            'routeName' => $routeName,
        ]);

        $this->writeFile($path, $content);

        $this->info("Controller created successfully!");
        $this->line("Location: app/Controllers/{$name}.php");

        if ($isResource) {
            $this->line("Resource routes created:");
            $this->line("  GET    {$routePath}         - index");
            $this->line("  POST   {$routePath}         - store");
            $this->line("  GET    {$routePath}/{id}    - show");
            $this->line("  PUT    {$routePath}/{id}    - update");
            $this->line("  DELETE {$routePath}/{id}    - destroy");
        }

        return 0;
    }
}
