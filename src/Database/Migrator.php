<?php

namespace Nexus\Database;

/**
 * Database Migrator
 *
 * Handles running and rolling back migrations
 */
class Migrator
{
    protected Database $db;
    protected string $migrationsPath;
    protected string $migrationsTable = 'migrations';

    public function __construct(Database $db, string $migrationsPath)
    {
        $this->db = $db;
        $this->migrationsPath = $migrationsPath;
        $this->ensureMigrationsTableExists();
    }

    /**
     * Create migrations table if it doesn't exist
     */
    protected function ensureMigrationsTableExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->migrationsTable}` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT NOT NULL,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->execute($sql);
    }

    /**
     * Run pending migrations
     *
     * @return array Executed migrations
     */
    public function run(): array
    {
        $executed = [];
        $migrations = $this->getPendingMigrations();

        if (empty($migrations)) {
            return $executed;
        }

        $batch = $this->getNextBatchNumber();

        foreach ($migrations as $migration) {
            $this->runMigration($migration, $batch);
            $executed[] = $migration;
        }

        return $executed;
    }

    /**
     * Run a single migration file
     *
     * @param string $migration Migration filename
     * @param int $batch Batch number
     */
    protected function runMigration(string $migration, int $batch): void
    {
        $migrationClass = $this->loadMigration($migration);

        if (!$migrationClass) {
            throw new \Exception("Migration class not found in file: {$migration}");
        }

        $instance = new $migrationClass($this->db);
        $instance->up();

        // Record the migration
        $this->db->execute(
            "INSERT INTO `{$this->migrationsTable}` (`migration`, `batch`) VALUES (?, ?)",
            [$migration, $batch]
        );
    }

    /**
     * Rollback the last batch of migrations
     *
     * @param int $steps Number of batches to rollback
     * @return array Rolled back migrations
     */
    public function rollback(int $steps = 1): array
    {
        $rolledBack = [];
        $batches = $this->getMigratedBatches();

        if (empty($batches)) {
            return $rolledBack;
        }

        $targetBatch = max($batches) - $steps + 1;

        $migrations = $this->db->query(
            "SELECT `migration` FROM `{$this->migrationsTable}`
             WHERE `batch` >= ?
             ORDER BY `id` DESC",
            [$targetBatch]
        );

        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration['migration']);
            $rolledBack[] = $migration['migration'];
        }

        return $rolledBack;
    }

    /**
     * Rollback a single migration
     *
     * @param string $migration Migration filename
     */
    protected function rollbackMigration(string $migration): void
    {
        $migrationClass = $this->loadMigration($migration);

        if (!$migrationClass) {
            throw new \Exception("Migration class not found in file: {$migration}");
        }

        $instance = new $migrationClass($this->db);
        $instance->down();

        // Remove the migration record
        $this->db->execute(
            "DELETE FROM `{$this->migrationsTable}` WHERE `migration` = ?",
            [$migration]
        );
    }

    /**
     * Reset all migrations
     *
     * @return array Rolled back migrations
     */
    public function reset(): array
    {
        $rolledBack = [];
        $migrations = $this->db->query(
            "SELECT `migration` FROM `{$this->migrationsTable}` ORDER BY `id` DESC"
        );

        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration['migration']);
            $rolledBack[] = $migration['migration'];
        }

        return $rolledBack;
    }

    /**
     * Get pending migrations
     *
     * @return array
     */
    protected function getPendingMigrations(): array
    {
        $allMigrations = $this->getAllMigrationFiles();
        $ranMigrations = $this->getRanMigrations();

        return array_diff($allMigrations, $ranMigrations);
    }

    /**
     * Get all migration files
     *
     * @return array
     */
    protected function getAllMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = scandir($this->migrationsPath);
        $migrations = [];

        foreach ($files as $file) {
            if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_.+\.php$/', $file)) {
                $migrations[] = $file;
            }
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Get migrations that have been run
     *
     * @return array
     */
    protected function getRanMigrations(): array
    {
        $results = $this->db->query(
            "SELECT `migration` FROM `{$this->migrationsTable}` ORDER BY `id`"
        );

        return array_column($results, 'migration');
    }

    /**
     * Get migrated batches
     *
     * @return array
     */
    protected function getMigratedBatches(): array
    {
        $results = $this->db->query(
            "SELECT DISTINCT `batch` FROM `{$this->migrationsTable}` ORDER BY `batch`"
        );

        return array_column($results, 'batch');
    }

    /**
     * Get next batch number
     *
     * @return int
     */
    protected function getNextBatchNumber(): int
    {
        $result = $this->db->query(
            "SELECT MAX(`batch`) as max_batch FROM `{$this->migrationsTable}`"
        );

        return ($result[0]['max_batch'] ?? 0) + 1;
    }

    /**
     * Load a migration file and return the class name
     *
     * @param string $file Migration filename
     * @return string|null
     */
    protected function loadMigration(string $file): ?string
    {
        $path = $this->migrationsPath . DIRECTORY_SEPARATOR . $file;

        if (!file_exists($path)) {
            return null;
        }

        require_once $path;

        // Extract class name from filename
        // Format: YYYY_MM_DD_HHMMSS_migration_name.php
        $parts = explode('_', $file);
        array_shift($parts); // Remove date
        array_shift($parts); // Remove month
        array_shift($parts); // Remove day
        array_shift($parts); // Remove time

        $className = str_replace('.php', '', implode('_', $parts));
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $className)));

        // Check if class exists
        $possibleClasses = [
            $className,
            "App\\Database\\Migrations\\{$className}",
        ];

        foreach ($possibleClasses as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Get migration status
     *
     * @return array
     */
    public function status(): array
    {
        $allMigrations = $this->getAllMigrationFiles();
        $ranMigrations = $this->db->query(
            "SELECT `migration`, `batch`, `executed_at` FROM `{$this->migrationsTable}` ORDER BY `id`"
        );

        $ranMap = [];
        foreach ($ranMigrations as $migration) {
            $ranMap[$migration['migration']] = [
                'batch' => $migration['batch'],
                'executed_at' => $migration['executed_at']
            ];
        }

        $status = [];
        foreach ($allMigrations as $migration) {
            $status[] = [
                'migration' => $migration,
                'ran' => isset($ranMap[$migration]),
                'batch' => $ranMap[$migration]['batch'] ?? null,
                'executed_at' => $ranMap[$migration]['executed_at'] ?? null
            ];
        }

        return $status;
    }
}
