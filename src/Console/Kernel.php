<?php

namespace Nexus\Console;

use Nexus\Core\Application;

class Kernel
{
    protected array $commands = [];

    public function __construct(
        protected Application $app
    ) {
        $this->registerDefaultCommands();
    }

    /**
     * Register default commands
     */
    protected function registerDefaultCommands(): void
    {
        $this->commands = [
            'make:controller' => Commands\MakeControllerCommand::class,
            'make:model' => Commands\MakeModelCommand::class,
            'make:middleware' => Commands\MakeMiddlewareCommand::class,
            'make:package' => Commands\MakePackageCommand::class,
            'make:module' => Commands\MakeModuleCommand::class,
            'make:command' => Commands\MakeCommandCommand::class,
            'make:provider' => Commands\MakeProviderCommand::class,
            'make:validation' => Commands\MakeValidationCommand::class,
            'routes:list' => Commands\RoutesListCommand::class,
            'view:clear' => Commands\ViewClearCommand::class,
            'storage:link' => Commands\StorageLinkCommand::class,
            'serve' => Commands\ServeCommand::class,
            'list' => Commands\ListCommand::class,
            'down' => Commands\DownCommand::class,
            'up' => Commands\UpCommand::class,
        ];
    }

    /**
     * Register a command
     */
    public function register(string $name, string $class): void
    {
        $this->commands[$name] = $class;
    }

    /**
     * Handle console command
     */
    public function handle(array $argv): int
    {
        array_shift($argv); // Remove script name

        if (empty($argv)) {
            return $this->runCommand('list', [], []);
        }

        $commandName = array_shift($argv);

        // Parse arguments and options
        [$arguments, $options] = $this->parseArguments($argv);

        return $this->runCommand($commandName, $arguments, $options);
    }

    /**
     * Run a command
     */
    protected function runCommand(string $name, array $arguments, array $options): int
    {
        if (!isset($this->commands[$name])) {
            echo "\033[31mCommand '{$name}' not found.\033[0m" . PHP_EOL;
            echo "Run 'php nexus list' to see available commands." . PHP_EOL;
            return 1;
        }

        $commandClass = $this->commands[$name];
        $command = $this->app->make($commandClass);

        $command->setArguments($arguments);
        $command->setOptions($options);

        try {
            return $command->handle();
        } catch (\Exception $e) {
            echo "\033[31mError: {$e->getMessage()}\033[0m" . PHP_EOL;
            return 1;
        }
    }

    /**
     * Parse command arguments and options
     */
    protected function parseArguments(array $argv): array
    {
        $arguments = [];
        $options = [];
        $argumentIndex = 0;

        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                // Long option
                $parts = explode('=', substr($arg, 2), 2);
                $options[$parts[0]] = $parts[1] ?? true;
            } elseif (str_starts_with($arg, '-')) {
                // Short option
                $options[substr($arg, 1)] = true;
            } else {
                // Argument
                $arguments[$argumentIndex++] = $arg;
            }
        }

        return [$arguments, $options];
    }

    /**
     * Get all registered commands
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
