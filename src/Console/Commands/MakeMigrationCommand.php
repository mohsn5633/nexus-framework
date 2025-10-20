<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakeMigrationCommand extends Command
{
    protected string $signature = 'make:migration {name}';
    protected string $description = 'Create a new migration file';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (!$name) {
            $this->error('Migration name is required');
            return 1;
        }

        $migrationPath = base_path('database/migrations');

        if (!is_dir($migrationPath)) {
            mkdir($migrationPath, 0755, true);
        }

        // Generate timestamp
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $filepath = $migrationPath . DIRECTORY_SEPARATOR . $filename;

        // Convert snake_case to PascalCase for class name
        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        // Generate migration template
        $template = $this->getMigrationTemplate($className);

        file_put_contents($filepath, $template);

        $this->success("Migration created successfully: {$filename}");

        return 0;
    }

    protected function getMigrationTemplate(string $className): string
    {
        return <<<PHP
<?php

use Nexus\Database\Migration;

class {$className} extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        \$this->create('table_name', function (\$table) {
            \$table->id();
            \$table->string('name');
            \$table->timestamps();
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        \$this->dropIfExists('table_name');
    }
}

PHP;
    }
}
