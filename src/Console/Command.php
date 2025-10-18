<?php

namespace Nexus\Console;

use Nexus\Core\Application;

abstract class Command
{
    protected string $signature = '';
    protected string $description = '';
    protected array $arguments = [];
    protected array $options = [];

    public function __construct(
        protected Application $app
    ) {
    }

    /**
     * Execute the command
     */
    abstract public function handle(): int;

    /**
     * Get command signature
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * Get command description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set command arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * Set command options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Get an argument value
     */
    protected function argument(string $key): mixed
    {
        return $this->arguments[$key] ?? null;
    }

    /**
     * Get an option value
     */
    protected function option(string $key): mixed
    {
        return $this->options[$key] ?? null;
    }

    /**
     * Write output to console
     */
    protected function info(string $message): void
    {
        echo "\033[32m" . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Write error to console
     */
    protected function error(string $message): void
    {
        echo "\033[31m" . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Write warning to console
     */
    protected function warn(string $message): void
    {
        echo "\033[33m" . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Write warning to console (alias)
     */
    protected function warning(string $message): void
    {
        $this->warn($message);
    }

    /**
     * Write success message to console
     */
    protected function success(string $message): void
    {
        echo "\033[32m✓ " . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Write line to console
     */
    protected function line(string $message = ''): void
    {
        echo $message . PHP_EOL;
    }

    /**
     * Ask for user input
     */
    protected function ask(string $question): string
    {
        echo $question . ': ';
        return trim(fgets(STDIN));
    }

    /**
     * Ask for confirmation
     */
    protected function confirm(string $question): bool
    {
        echo $question . ' (yes/no): ';
        $answer = strtolower(trim(fgets(STDIN)));
        return in_array($answer, ['yes', 'y']);
    }

    /**
     * Create a directory if it doesn't exist
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Write file with content
     */
    protected function writeFile(string $path, string $content): bool
    {
        $this->ensureDirectoryExists(dirname($path));
        return file_put_contents($path, $content) !== false;
    }

    /**
     * Check if file exists
     */
    protected function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Get stub content
     */
    protected function getStub(string $name): string
    {
        $stubPath = $this->app->basePath("src/Console/stubs/{$name}.stub");

        if (!file_exists($stubPath)) {
            return '';
        }

        return file_get_contents($stubPath);
    }

    /**
     * Replace placeholders in stub
     */
    protected function replaceInStub(string $stub, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $stub = str_replace("{{{$key}}}", $value, $stub);
        }

        return $stub;
    }
}
