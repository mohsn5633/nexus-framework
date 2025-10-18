<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakeModelCommand extends Command
{
    protected string $signature = 'make:model';
    protected string $description = 'Create a new model class';

    public function handle(): int
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Model name is required!');
            $this->line('Usage: php nexus make:model ModelName [--table=table_name]');
            return 1;
        }

        $path = $this->app->basePath("app/Models/{$name}.php");

        if ($this->fileExists($path)) {
            $this->error("Model {$name} already exists!");
            return 1;
        }

        // Determine table name
        $tableName = $this->option('table');
        if (!$tableName) {
            // Convert to snake_case plural
            $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name)) . 's';
        }

        $stub = $this->getStub('model');

        $content = $this->replaceInStub($stub, [
            'namespace' => 'App\\Models',
            'class' => $name,
            'table' => $tableName,
        ]);

        $this->writeFile($path, $content);

        $this->info("Model created successfully!");
        $this->line("Location: app/Models/{$name}.php");
        $this->line("Table: {$tableName}");

        return 0;
    }
}
