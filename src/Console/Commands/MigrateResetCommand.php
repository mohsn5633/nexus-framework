<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;
use Nexus\Database\Database;
use Nexus\Database\Migrator;

class MigrateResetCommand extends Command
{
    protected string $signature = 'migrate:reset';
    protected string $description = 'Rollback all database migrations';

    public function handle(): int
    {
        $this->info('Rolling back all migrations...');

        $migrationsPath = base_path('database/migrations');
        $db = app(Database::class);
        $migrator = new Migrator($db, $migrationsPath);

        try {
            $rolledBack = $migrator->reset();

            if (empty($rolledBack)) {
                $this->info('Nothing to rollback.');
                return 0;
            }

            $this->success('All migrations rolled back successfully:');
            foreach ($rolledBack as $migration) {
                $this->line("  ✓ {$migration}");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Reset failed: ' . $e->getMessage());
            return 1;
        }
    }
}
