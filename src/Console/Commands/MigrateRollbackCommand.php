<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;
use Nexus\Database\Database;
use Nexus\Database\Migrator;

class MigrateRollbackCommand extends Command
{
    protected string $signature = 'migrate:rollback {--steps=1}';
    protected string $description = 'Rollback the last database migration';

    public function handle(): int
    {
        $steps = (int) $this->option('steps', 1);

        $this->info("Rolling back last {$steps} batch(es) of migrations...");

        $migrationsPath = base_path('database/migrations');
        $db = app(Database::class);
        $migrator = new Migrator($db, $migrationsPath);

        try {
            $rolledBack = $migrator->rollback($steps);

            if (empty($rolledBack)) {
                $this->info('Nothing to rollback.');
                return 0;
            }

            $this->success('Rollback completed successfully:');
            foreach ($rolledBack as $migration) {
                $this->line("  ✓ {$migration}");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Rollback failed: ' . $e->getMessage());
            return 1;
        }
    }
}
