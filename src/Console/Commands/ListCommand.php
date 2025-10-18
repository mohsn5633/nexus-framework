<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class ListCommand extends Command
{
    protected string $signature = 'list';
    protected string $description = 'List all available commands';

    public function handle(): int
    {
        $this->line('');
        $this->line("\033[1m\033[36m  _   _                     \033[0m");
        $this->line("\033[1m\033[36m | \\ | | _____  ___   _ ___ \033[0m");
        $this->line("\033[1m\033[36m |  \\| |/ _ \\ \\/ / | | / __|\033[0m");
        $this->line("\033[1m\033[36m | |\\  |  __/>  <| |_| \\__ \\\033[0m");
        $this->line("\033[1m\033[36m |_| \\_|\\___/_/\\_\\\\__,_|___/\033[0m");
        $this->line('');
        $this->line("\033[33mNexus Framework\033[0m - CLI Tool");
        $this->line('');

        $kernel = $this->app->make(\Nexus\Console\Kernel::class);
        $commands = $kernel->getCommands();

        $groups = [
            'make' => [],
            'routes' => [],
            'other' => [],
        ];

        foreach ($commands as $name => $class) {
            $command = $this->app->make($class);
            $group = 'other';

            if (str_starts_with($name, 'make:')) {
                $group = 'make';
            } elseif (str_starts_with($name, 'routes:')) {
                $group = 'routes';
            }

            $groups[$group][$name] = $command->getDescription();
        }

        // Print make commands
        if (!empty($groups['make'])) {
            $this->info('Make Commands:');
            foreach ($groups['make'] as $name => $description) {
                $this->printCommand($name, $description);
            }
            $this->line('');
        }

        // Print route commands
        if (!empty($groups['routes'])) {
            $this->info('Route Commands:');
            foreach ($groups['routes'] as $name => $description) {
                $this->printCommand($name, $description);
            }
            $this->line('');
        }

        // Print other commands
        if (!empty($groups['other'])) {
            $this->info('Other Commands:');
            foreach ($groups['other'] as $name => $description) {
                $this->printCommand($name, $description);
            }
            $this->line('');
        }

        $this->line('Usage:');
        $this->line('  php nexus <command> [arguments] [options]');
        $this->line('');
        $this->line('Examples:');
        $this->line('  php nexus make:controller UserController --resource');
        $this->line('  php nexus make:model Post --table=posts');
        $this->line('  php nexus routes:list');
        $this->line('  php nexus serve --port=8080');
        $this->line('');

        return 0;
    }

    protected function printCommand(string $name, string $description): void
    {
        $this->line(sprintf('  \033[32m%-25s\033[0m %s', $name, $description));
    }
}
