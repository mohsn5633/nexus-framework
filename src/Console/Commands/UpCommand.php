<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class UpCommand extends Command
{
    protected string $signature = 'up';
    protected string $description = 'Bring the application out of maintenance mode';

    public function handle(): int
    {
        $maintenanceFile = $this->app->basePath('storage/framework/down');

        if (!file_exists($maintenanceFile)) {
            $this->warning('Application is not in maintenance mode.');
            return 0;
        }

        unlink($maintenanceFile);

        $this->success('Application is now live.');
        $this->line('');
        $this->info('Note: Users with stored bypass keys in their sessions will need to clear cookies or restart their browser.');

        return 0;
    }
}
