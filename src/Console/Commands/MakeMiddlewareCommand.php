<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakeMiddlewareCommand extends Command
{
    protected string $signature = 'make:middleware';
    protected string $description = 'Create a new middleware class';

    public function handle(): int
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Middleware name is required!');
            $this->line('Usage: php nexus make:middleware MiddlewareName');
            return 1;
        }

        // Ensure it ends with "Middleware"
        if (!str_ends_with($name, 'Middleware')) {
            $name .= 'Middleware';
        }

        $namespace = $this->option('framework')
            ? 'Nexus\\Http\\Middleware'
            : 'App\\Middleware';

        $directory = $this->option('framework')
            ? 'src/Http/Middleware'
            : 'app/Middleware';

        $path = $this->app->basePath("{$directory}/{$name}.php");

        if ($this->fileExists($path)) {
            $this->error("Middleware {$name} already exists!");
            return 1;
        }

        $stub = $this->getStub('middleware');

        $content = $this->replaceInStub($stub, [
            'namespace' => $namespace,
            'class' => $name,
        ]);

        $this->writeFile($path, $content);

        $this->info("Middleware created successfully!");
        $this->line("Location: {$directory}/{$name}.php");
        $this->line("Apply to routes: ->middleware([{$namespace}\\{$name}::class])");

        return 0;
    }
}
