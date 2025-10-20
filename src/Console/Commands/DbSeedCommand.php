<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;
use Nexus\Database\Database;

class DbSeedCommand extends Command
{
    protected string $signature = 'db:seed {--class=DatabaseSeeder}';
    protected string $description = 'Seed the database with records';

    public function handle(): int
    {
        $className = $this->option('class', 'DatabaseSeeder');

        $this->info("Seeding database with: {$className}");

        $seederPath = base_path('database/seeders');

        if (!is_dir($seederPath)) {
            $this->error('Seeders directory not found: ' . $seederPath);
            return 1;
        }

        try {
            $seederFile = $seederPath . DIRECTORY_SEPARATOR . $className . '.php';

            if (!file_exists($seederFile)) {
                $this->error("Seeder not found: {$className}");
                return 1;
            }

            require_once $seederFile;

            if (!class_exists($className)) {
                $this->error("Seeder class not found: {$className}");
                return 1;
            }

            $db = app(Database::class);
            $seeder = new $className($db);
            $seeder->run();

            $this->success('Database seeded successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Seeding failed: ' . $e->getMessage());
            return 1;
        }
    }
}
