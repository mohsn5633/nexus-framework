<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakeSeederCommand extends Command
{
    protected string $signature = 'make:seeder {name}';
    protected string $description = 'Create a new seeder class';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (!$name) {
            $this->error('Seeder name is required');
            return 1;
        }

        // Ensure name ends with 'Seeder'
        if (!str_ends_with($name, 'Seeder')) {
            $name .= 'Seeder';
        }

        $seederPath = base_path('database/seeders');

        if (!is_dir($seederPath)) {
            mkdir($seederPath, 0755, true);
        }

        $filename = "{$name}.php";
        $filepath = $seederPath . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($filepath)) {
            $this->error("Seeder already exists: {$filename}");
            return 1;
        }

        // Generate seeder template
        $template = $this->getSeederTemplate($name);

        file_put_contents($filepath, $template);

        $this->success("Seeder created successfully: {$filename}");

        return 0;
    }

    protected function getSeederTemplate(string $className): string
    {
        return <<<PHP
<?php

use Nexus\Database\Seeder;

class {$className} extends Seeder
{
    /**
     * Run the seeder
     */
    public function run(): void
    {
        // Example: Insert data
        \$this->insert('users', [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);

        // Or use query builder
        // \$this->table('users')->insert([...]);

        // Call other seeders
        // \$this->call([
        //     PostsSeeder::class,
        //     CommentsSeeder::class,
        // ]);
    }
}

PHP;
    }
}
