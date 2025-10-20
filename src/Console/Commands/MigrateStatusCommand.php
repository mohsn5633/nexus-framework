<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;
use Nexus\Database\Database;
use Nexus\Database\Migrator;

class MigrateStatusCommand extends Command
{
    protected string $signature = 'migrate:status';
    protected string $description = 'Show the status of each migration';

    public function handle(): int
    {
        $migrationsPath = base_path('database/migrations');

        if (!is_dir($migrationsPath)) {
            $this->error('Migrations directory not found: ' . $migrationsPath);
            return 1;
        }

        $db = app(Database::class);
        $migrator = new Migrator($db, $migrationsPath);

        try {
            $status = $migrator->status();

            if (empty($status)) {
                $this->info('No migrations found.');
                return 0;
            }

            $this->info('Migration Status:');
            $this->line('');

            // Header
            $this->line(sprintf(
                '%-6s | %-50s | %-10s | %s',
                'Status',
                'Migration',
                'Batch',
                'Executed At'
            ));
            $this->line(str_repeat('-', 100));

            // Rows
            foreach ($status as $migration) {
                $statusIcon = $migration['ran'] ? '✓ Ran' : '✗ Pending';
                $batch = $migration['batch'] ?? '-';
                $executedAt = $migration['executed_at'] ?? '-';

                $this->line(sprintf(
                    '%-6s | %-50s | %-10s | %s',
                    $statusIcon,
                    substr($migration['migration'], 0, 50),
                    $batch,
                    $executedAt
                ));
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to get migration status: ' . $e->getMessage());
            return 1;
        }
    }
}
