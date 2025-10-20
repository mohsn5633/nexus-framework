<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakeJobCommand extends Command
{
    protected string $signature = 'make:job {name}';
    protected string $description = 'Create a new job class';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (!$name) {
            $this->error('Job name is required');
            return 1;
        }

        // Ensure name ends with 'Job'
        if (!str_ends_with($name, 'Job')) {
            $name .= 'Job';
        }

        $jobsPath = base_path('app/Jobs');

        if (!is_dir($jobsPath)) {
            mkdir($jobsPath, 0755, true);
        }

        $filename = "{$name}.php";
        $filepath = $jobsPath . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($filepath)) {
            $this->error("Job already exists: {$filename}");
            return 1;
        }

        // Generate job template
        $template = $this->getJobTemplate($name);

        file_put_contents($filepath, $template);

        $this->success("Job created successfully: {$filename}");

        return 0;
    }

    protected function getJobTemplate(string $className): string
    {
        return <<<PHP
<?php

namespace App\Jobs;

use Nexus\Queue\Dispatchable;

class {$className}
{
    use Dispatchable;

    /**
     * Execute the job
     */
    public function handle(): void
    {
        // Job logic here
    }

    /**
     * Handle job failure
     */
    public function failed(\Exception \$exception): void
    {
        // Handle failure
    }
}

PHP;
    }
}
