<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class QueueTableCommand extends Command
{
    protected string $signature = 'queue:table';
    protected string $description = 'Create migration for the queue jobs database table';

    public function handle(): int
    {
        $migrationsPath = base_path('database/migrations');

        if (!is_dir($migrationsPath)) {
            mkdir($migrationsPath, 0755, true);
        }

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_create_jobs_table.php";
        $filepath = $migrationsPath . DIRECTORY_SEPARATOR . $filename;

        $template = $this->getMigrationTemplate();

        file_put_contents($filepath, $template);

        $this->success("Migration created successfully: {$filename}");
        $this->info("Run 'php nexus migrate' to create the jobs table");

        return 0;
    }

    protected function getMigrationTemplate(): string
    {
        return <<<'PHP'
<?php

use Nexus\Database\Migration;

class CreateJobsTable extends Migration
{
    public function up(): void
    {
        // Create jobs table
        $this->create('jobs', function ($table) {
            $table->id();
            $table->string('queue')->index();
            $table->text('payload');
            $table->integer('attempts')->default(0)->unsigned();
            $table->integer('reserved_at')->nullable()->unsigned();
            $table->integer('available_at')->unsigned();
            $table->integer('created_at')->unsigned();
        });

        // Create failed_jobs table
        $this->create('failed_jobs', function ($table) {
            $table->id();
            $table->string('queue');
            $table->text('payload');
            $table->text('exception');
            $table->integer('failed_at')->unsigned();
        });
    }

    public function down(): void
    {
        $this->dropIfExists('jobs');
        $this->dropIfExists('failed_jobs');
    }
}

PHP;
    }
}
