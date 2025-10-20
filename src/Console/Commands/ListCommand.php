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
            'migrate' => [],
            'database' => [],
            'queue' => [],
            'schedule' => [],
            'routes' => [],
            'view' => [],
            'storage' => [],
            'websocket' => [],
            'worker' => [],
            'server' => [],
            'other' => [],
        ];

        foreach ($commands as $name => $class) {
            $command = $this->app->make($class);
            $group = 'other';

            if (str_starts_with($name, 'make:')) {
                $group = 'make';
            } elseif (str_starts_with($name, 'migrate')) {
                $group = 'migrate';
            } elseif (str_starts_with($name, 'db:')) {
                $group = 'database';
            } elseif (str_starts_with($name, 'queue:')) {
                $group = 'queue';
            } elseif (str_starts_with($name, 'schedule:')) {
                $group = 'schedule';
            } elseif (str_starts_with($name, 'routes:')) {
                $group = 'routes';
            } elseif (str_starts_with($name, 'view:')) {
                $group = 'view';
            } elseif (str_starts_with($name, 'storage:')) {
                $group = 'storage';
            } elseif (str_starts_with($name, 'websocket:')) {
                $group = 'websocket';
            } elseif (str_starts_with($name, 'worker:')) {
                $group = 'worker';
            } elseif (in_array($name, ['serve', 'down', 'up'])) {
                $group = 'server';
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

        // Print migration commands
        if (!empty($groups['migrate'])) {
            $this->info('Migration Commands:');
            foreach ($groups['migrate'] as $name => $description) {
                $this->printCommand($name, $description);
            }
            $this->line('');
        }

        // Print database commands
        if (!empty($groups['database'])) {
            $this->info('Database Commands:');
            foreach ($groups['database'] as $name => $description) {
                $this->printCommand($name, $description);
            }
            $this->line('');
        }

        // Print queue commands
        if (!empty($groups['queue'])) {
            $this->info('Queue Commands:');
            foreach ($groups['queue'] as $name => $description) {
                $this->printCommand($name, $description);
            }
            $this->line('');
        }

        // Print schedule commands
        if (!empty($groups['schedule'])) {
            $this->info('Schedule Commands:');
            foreach ($groups['schedule'] as $name => $description) {
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

        // Print view commands
        if (!empty($groups['view'])) {
            $this->info('View Commands:');
            foreach ($groups['view'] as $name => $description) {
                $this->printCommand($name, $description);
            }
            $this->line('');
        }

        // Print storage commands
        if (!empty($groups['storage'])) {
            $this->info('Storage Commands:');
            foreach ($groups['storage'] as $name => $description) {
                $this->printCommand($name, $description);
            }
            $this->line('');
        }

        // Print websocket commands
        if (!empty($groups['websocket'])) {
            $this->info('WebSocket Commands:');
            foreach ($groups['websocket'] as $name => $description) {
                $this->printCommand($name, $description);
            }
            $this->line('');
        }

        // Print worker commands
        if (!empty($groups['worker'])) {
            $this->info('Worker Commands:');
            foreach ($groups['worker'] as $name => $description) {
                $this->printCommand($name, $description);
            }
            $this->line('');
        }

        // Print server commands
        if (!empty($groups['server'])) {
            $this->info('Server Commands:');
            foreach ($groups['server'] as $name => $description) {
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
        $this->line('  php nexus make:migration create_users_table');
        $this->line('  php nexus make:job ProcessPayment');
        $this->line('  php nexus migrate');
        $this->line('  php nexus db:seed');
        $this->line('  php nexus queue:work');
        $this->line('  php nexus schedule:run');
        $this->line('  php nexus websocket:serve --host=0.0.0.0 --port=8080');
        $this->line('  php nexus serve --port=8080');
        $this->line('');

        return 0;
    }

    protected function printCommand(string $name, string $description): void
    {
        $this->line(sprintf('  %-25s %s', $name, $description));
    }
}
