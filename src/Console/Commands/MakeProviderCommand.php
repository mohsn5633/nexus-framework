<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakeProviderCommand extends Command
{
    protected string $signature = 'make:provider';
    protected string $description = 'Create a new service provider class';

    public function handle(): int
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Provider name is required.');
            $this->line('Usage: php nexus make:provider <ProviderName>');
            return 1;
        }

        // Add Provider suffix if not present
        if (!str_ends_with($name, 'Provider')) {
            $name .= 'Provider';
        }

        $path = $this->app->basePath("app/Providers/{$name}.php");

        if ($this->fileExists($path)) {
            $this->error("Provider already exists: {$name}");
            return 1;
        }

        $stub = $this->getStub('provider');

        if (empty($stub)) {
            $stub = $this->getDefaultStub();
        }

        $content = $this->replaceInStub($stub, [
            'ProviderName' => $name,
        ]);

        if ($this->writeFile($path, $content)) {
            $this->success("Provider created successfully: {$name}");
            $this->line('');
            $this->info("Add this provider to config/app.php:");
            $this->line("    App\\Providers\\{$name}::class,");
            return 0;
        }

        $this->error("Failed to create provider: {$name}");
        return 1;
    }

    protected function getDefaultStub(): string
    {
        return <<<'EOT'
<?php

namespace App\Providers;

use Nexus\Core\ServiceProvider;

class {{ProviderName}} extends ServiceProvider
{
    /**
     * Register any application services
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        //
    }
}
EOT;
    }
}
