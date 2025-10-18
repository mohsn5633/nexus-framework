<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakeCommandCommand extends Command
{
    protected string $signature = 'make:command';
    protected string $description = 'Create a new console command';

    public function handle(): int
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Command name is required!');
            $this->line('Usage: php nexus make:command CommandName');
            return 1;
        }

        // Ensure it ends with "Command"
        if (!str_ends_with($name, 'Command')) {
            $name .= 'Command';
        }

        $path = $this->app->basePath("app/Commands/{$name}.php");

        if ($this->fileExists($path)) {
            $this->error("Command {$name} already exists!");
            return 1;
        }

        // Generate signature
        $signature = $this->option('signature');
        if (!$signature) {
            $signature = strtolower(str_replace('Command', '', $name));
            $signature = preg_replace('/(?<!^)[A-Z]/', ':$0', $signature);
            $signature = strtolower($signature);
        }

        $description = $this->option('description') ?? "Execute {$name}";

        $stub = $this->getStub('command');

        $content = $this->replaceInStub($stub, [
            'namespace' => 'App\\Commands',
            'class' => $name,
            'signature' => $signature,
            'description' => $description,
        ]);

        $this->writeFile($path, $content);

        $this->info("Command created successfully!");
        $this->line("Location: app/Commands/{$name}.php");
        $this->line("Signature: {$signature}");
        $this->line('');
        $this->line("To register the command, add it to src/Console/Kernel.php:");
        $this->line("  '{$signature}' => App\\Commands\\{$name}::class,");

        return 0;
    }
}
