<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakePackageCommand extends Command
{
    protected string $signature = 'make:package';
    protected string $description = 'Create a new package';

    public function handle(): int
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Package name is required!');
            $this->line('Usage: php nexus make:package PackageName');
            return 1;
        }

        $packageDir = $this->app->basePath("packages/{$name}");

        if (is_dir($packageDir)) {
            $this->error("Package {$name} already exists!");
            return 1;
        }

        // Create package directory structure
        $directories = [
            $packageDir,
            "{$packageDir}/Controllers",
            "{$packageDir}/Models",
            "{$packageDir}/Views",
            "{$packageDir}/Middleware",
        ];

        foreach ($directories as $dir) {
            $this->ensureDirectoryExists($dir);
        }

        // Create Package.php
        $stub = $this->getStub('package');
        $slug = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));

        $content = $this->replaceInStub($stub, [
            'name' => $name,
            'slug' => $slug,
        ]);

        $this->writeFile("{$packageDir}/Package.php", $content);

        // Create README
        $readme = "# {$name} Package\n\nA custom package for Nexus Framework.\n\n## Installation\n\nThis package is auto-loaded from the `packages/` directory.\n";
        $this->writeFile("{$packageDir}/README.md", $readme);

        $this->info("Package created successfully!");
        $this->line("Location: packages/{$name}/");
        $this->line("Package class: Packages\\{$name}\\Package");
        $this->line("Test route: /{$slug}");

        return 0;
    }
}
