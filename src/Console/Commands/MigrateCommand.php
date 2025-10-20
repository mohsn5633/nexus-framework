<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;
use Nexus\Database\Database;
use Nexus\Database\Migrator;

class MigrateCommand extends Command
{
    protected string $signature = 'migrate';
    protected string $description = 'Run database migrations';

    public function handle(): int
    {
        $this->info('Running migrations...');

        $migrationsPath = base_path('database/migrations');

        if (!is_dir($migrationsPath)) {
            $this->error('Migrations directory not found: ' . $migrationsPath);
            return 1;
        }

        $db = app(Database::class);
        $migrator = new Migrator($db, $migrationsPath);

        try {
            $executed = $migrator->run();

            if (empty($executed)) {
                $this->info('Nothing to migrate.');
                return 0;
            }

            $this->success('Migrations completed successfully:');
            foreach ($executed as $migration) {
                $this->line("  ✓ {$migration}");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}
