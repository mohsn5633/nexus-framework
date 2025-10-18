<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakeModuleCommand extends Command
{
    protected string $signature = 'make:module';
    protected string $description = 'Create a new module with controller, model, and routes';

    public function handle(): int
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Module name is required!');
            $this->line('Usage: php nexus make:module ModuleName');
            return 1;
        }

        $this->info("Creating module: {$name}");
        $this->line('');

        // Create model
        $this->line('Creating model...');
        $modelPath = $this->app->basePath("app/Models/{$name}.php");
        if (!$this->fileExists($modelPath)) {
            $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name)) . 's';
            $stub = $this->getStub('model');
            $content = $this->replaceInStub($stub, [
                'namespace' => 'App\\Models',
                'class' => $name,
                'table' => $tableName,
            ]);
            $this->writeFile($modelPath, $content);
            $this->info("  ✓ Model created: app/Models/{$name}.php");
        } else {
            $this->warn("  ⊗ Model already exists");
        }

        // Create controller
        $this->line('Creating controller...');
        $controllerName = "{$name}Controller";
        $controllerPath = $this->app->basePath("app/Controllers/{$controllerName}.php");
        if (!$this->fileExists($controllerPath)) {
            $stub = $this->getStub('controller.resource');
            $routePath = '/' . strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name)) . 's';
            $routeName = strtolower($name) . 's';

            $content = $this->replaceInStub($stub, [
                'namespace' => 'App\\Controllers',
                'class' => $controllerName,
                'route' => $routePath,
                'routeName' => $routeName,
            ]);
            $this->writeFile($controllerPath, $content);
            $this->info("  ✓ Controller created: app/Controllers/{$controllerName}.php");
        } else {
            $this->warn("  ⊗ Controller already exists");
        }

        $this->line('');
        $this->info("Module '{$name}' created successfully!");
        $this->line("You can now access the resource routes:");

        $routePath = '/' . strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name)) . 's';
        $this->line("  GET    {$routePath}");
        $this->line("  POST   {$routePath}");
        $this->line("  GET    {$routePath}/{id}");
        $this->line("  PUT    {$routePath}/{id}");
        $this->line("  DELETE {$routePath}/{id}");

        return 0;
    }
}
