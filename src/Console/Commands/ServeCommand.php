<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class ServeCommand extends Command
{
    protected string $signature = 'serve';
    protected string $description = 'Start the PHP built-in development server';

    public function handle(): int
    {
        $host = $this->option('host') ?? '127.0.0.1';
        $port = $this->option('port') ?? '8000';

        $this->info("Nexus development server started on http://{$host}:{$port}");
        $this->line("Press Ctrl+C to stop the server");
        $this->line('');

        $publicPath = $this->app->basePath('public');

        // Start PHP built-in server
        $command = sprintf(
            'php -S %s:%s -t %s',
            $host,
            $port,
            escapeshellarg($publicPath)
        );

        passthru($command, $exitCode);

        return $exitCode;
    }
}
