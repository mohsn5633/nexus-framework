<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakeValidationCommand extends Command
{
    protected string $signature = 'make:validation';
    protected string $description = 'Create a new validation class';

    public function handle(): int
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Validation name is required.');
            $this->line('Usage: php nexus make:validation <ValidationName>');
            return 1;
        }

        // Add Validation suffix if not present
        if (!str_ends_with($name, 'Validation')) {
            $name .= 'Validation';
        }

        $path = $this->app->basePath("app/Validations/{$name}.php");

        if ($this->fileExists($path)) {
            $this->error("Validation already exists: {$name}");
            return 1;
        }

        $stub = $this->getStub('validation');

        if (empty($stub)) {
            $stub = $this->getDefaultStub();
        }

        $content = $this->replaceInStub($stub, [
            'ValidationName' => $name,
        ]);

        if ($this->writeFile($path, $content)) {
            $this->success("Validation created successfully: {$name}");
            $this->line('');
            $this->info("File location: app/Validations/{$name}.php");
            return 0;
        }

        $this->error("Failed to create validation: {$name}");
        return 1;
    }

    protected function getDefaultStub(): string
    {
        return <<<'EOT'
<?php

namespace App\Validations;

use Nexus\Validation\Validator;

class {{ValidationName}}
{
    /**
     * Get the validation rules
     */
    public static function rules(): array
    {
        return [
            // Define your validation rules here
            // 'name' => 'required|string|max:255',
            // 'email' => 'required|email|unique:users,email',
        ];
    }

    /**
     * Get custom validation messages
     */
    public static function messages(): array
    {
        return [
            // Define custom error messages here
            // 'name.required' => 'Please provide your name.',
            // 'email.unique' => 'This email is already registered.',
        ];
    }

    /**
     * Validate the given data
     */
    public static function validate(array $data): array
    {
        $validator = new Validator($data, static::rules(), static::messages());
        return $validator->validate();
    }
}
EOT;
    }
}
