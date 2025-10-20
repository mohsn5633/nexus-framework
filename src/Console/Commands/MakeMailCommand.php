<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;

class MakeMailCommand extends Command
{
    protected string $signature = 'make:mail {name}';
    protected string $description = 'Create a new mailable class';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (!$name) {
            $this->error('Mailable name is required');
            return 1;
        }

        // Ensure name ends with 'Mail'
        if (!str_ends_with($name, 'Mail')) {
            $name .= 'Mail';
        }

        $mailPath = base_path('app/Mail');

        if (!is_dir($mailPath)) {
            mkdir($mailPath, 0755, true);
        }

        $filename = "{$name}.php";
        $filepath = $mailPath . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($filepath)) {
            $this->error("Mailable already exists: {$filename}");
            return 1;
        }

        // Generate mailable template
        $template = $this->getMailableTemplate($name);

        file_put_contents($filepath, $template);

        $this->success("Mailable created successfully: {$filename}");

        return 0;
    }

    protected function getMailableTemplate(string $className): string
    {
        return <<<PHP
<?php

namespace App\Mail;

use Nexus\Mail\Mailable;

class {$className} extends Mailable
{
    /**
     * Create a new message instance
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message
     */
    public function build(): void
    {
        \$this->subject('Your Subject Here')
            ->view('emails.template');
    }
}

PHP;
    }
}
