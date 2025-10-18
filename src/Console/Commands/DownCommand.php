<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class DownCommand extends Command
{
    protected string $signature = 'down';
    protected string $description = 'Put the application into maintenance mode';

    public function handle(): int
    {
        $secret = $this->generateSecret();
        $data = [
            'time' => time(),
            'message' => 'The application is currently down for maintenance.',
            'retry' => 60,
            'secret' => $secret,
        ];

        $maintenanceFile = $this->app->basePath('storage/framework/down');
        $this->ensureDirectoryExists(dirname($maintenanceFile));

        file_put_contents($maintenanceFile, json_encode($data, JSON_PRETTY_PRINT));

        $this->success('Application is now in maintenance mode.');
        $this->line('');
        $this->info('Bypass URL: ' . config('app.url') . '?secret=' . $secret);
        $this->line('');
        $this->line('Secret key: ' . $secret);

        return 0;
    }

    protected function generateSecret(): string
    {
        return bin2hex(random_bytes(16));
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
