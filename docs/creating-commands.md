# Creating Commands

Nexus Framework's CLI system allows you to create custom commands for automating tasks, generating code, and performing maintenance operations.

## Table of Contents

- [Introduction](#introduction)
- [Creating Commands](#creating-commands)
- [Command Structure](#command-structure)
- [Command Arguments](#command-arguments)
- [Command Options](#command-options)
- [Command Output](#command-output)
- [Complete Examples](#complete-examples)

## Introduction

Commands provide a way to interact with your application via the command line. They're perfect for:

- Data processing tasks
- Scheduled operations
- Database maintenance
- Email sending
- Report generation
- Custom utilities

## Creating Commands

### Using CLI (Recommended)

```bash
php nexus make:command SendEmails
php nexus make:command GenerateReport
php nexus make:command CleanupTempFiles
```

This creates a command class in `app/Commands/`.

### Manual Creation

Create a file in `app/Commands/`:

```php
<?php

namespace App\Commands;

use Nexus\Console\Command;

class SendEmailsCommand extends Command
{
    protected string $signature = 'emails:send';
    protected string $description = 'Send pending emails from the queue';

    public function handle(): int
    {
        $this->info('Sending emails...');

        // Your logic here

        $this->success('Emails sent successfully!');

        return 0;
    }
}
```

## Command Structure

### Basic Structure

```php
<?php

namespace App\Commands;

use Nexus\Console\Command;

class MyCommand extends Command
{
    /**
     * Command signature
     */
    protected string $signature = 'my:command';

    /**
     * Command description
     */
    protected string $description = 'Description of what the command does';

    /**
     * Execute the command
     *
     * @return int Exit code (0 = success, non-zero = error)
     */
    public function handle(): int
    {
        // Command logic

        return 0;  // Success
    }
}
```

### Registering Commands

Add your command to `src/Console/Kernel.php`:

```php
protected function registerDefaultCommands(): void
{
    $this->commands = [
        // ... existing commands
        'emails:send' => \App\Commands\SendEmailsCommand::class,
        'report:generate' => \App\Commands\GenerateReportCommand::class,
        'cleanup:temp' => \App\Commands\CleanupTempFilesCommand::class,
    ];
}
```

## Command Arguments

### Defining Arguments

```php
protected string $signature = 'user:create {name} {email}';

public function handle(): int
{
    $name = $this->argument('name');
    $email = $this->argument('email');

    $this->info("Creating user: {$name} ({$email})");

    // Create user logic

    return 0;
}
```

Usage:
```bash
php nexus user:create "John Doe" john@example.com
```

### Optional Arguments

```php
protected string $signature = 'user:create {name} {email?}';

public function handle(): int
{
    $name = $this->argument('name');
    $email = $this->argument('email') ?? 'no-email@example.com';

    return 0;
}
```

### Array Arguments

```php
protected string $signature = 'user:delete {ids*}';

public function handle(): int
{
    $ids = $this->argument('ids');

    foreach ($ids as $id) {
        $this->line("Deleting user {$id}");
    }

    return 0;
}
```

Usage:
```bash
php nexus user:delete 1 2 3 4 5
```

## Command Options

### Defining Options

```php
protected string $signature = 'user:list';

public function handle(): int
{
    $active = $this->option('active');
    $role = $this->option('role');

    if ($active) {
        $this->info('Showing only active users');
    }

    if ($role) {
        $this->info("Filtering by role: {$role}");
    }

    return 0;
}
```

Usage:
```bash
php nexus user:list --active
php nexus user:list --role=admin
php nexus user:list --active --role=admin
```

### Default Option Values

```php
public function handle(): int
{
    $limit = $this->option('limit') ?? 10;
    $format = $this->option('format') ?? 'table';

    return 0;
}
```

## Command Output

### Output Methods

```php
// Regular text
$this->line('Regular message');

// Info message (cyan)
$this->info('Processing...');

// Success message (green)
$this->success('Task completed!');

// Warning message (yellow)
$this->warning('This action is irreversible!');

// Error message (red)
$this->error('An error occurred!');
```

### Formatting Output

```php
$this->line('');  // Blank line
$this->info('=== Header ===');
$this->line('');
$this->line('Item 1');
$this->line('Item 2');
$this->line('Item 3');
```

## Complete Examples

### Send Emails Command

```php
<?php

namespace App\Commands;

use App\Models\Email;
use App\Services\Mailer;
use Nexus\Console\Command;

class SendEmailsCommand extends Command
{
    protected string $signature = 'emails:send {--limit=10}';
    protected string $description = 'Send pending emails from the queue';

    public function __construct(
        protected Mailer $mailer
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = $this->option('limit');

        $this->info("Sending up to {$limit} emails...");
        $this->line('');

        // Get pending emails
        $emails = Email::where('status', 'pending')
            ->limit($limit)
            ->get();

        if (empty($emails)) {
            $this->warning('No pending emails found.');
            return 0;
        }

        $sent = 0;
        $failed = 0;

        foreach ($emails as $email) {
            try {
                $this->line("Sending email to {$email['to']}...");

                $this->mailer->send(
                    $email['to'],
                    $email['subject'],
                    $email['body']
                );

                // Update status
                Email::where('id', $email['id'])
                    ->update(['status' => 'sent']);

                $sent++;
            } catch (\Exception $e) {
                $this->error("Failed to send email: {$e->getMessage()}");

                // Update status
                Email::where('id', $email['id'])
                    ->update(['status' => 'failed']);

                $failed++;
            }
        }

        $this->line('');
        $this->success("Successfully sent {$sent} emails");

        if ($failed > 0) {
            $this->warning("{$failed} emails failed to send");
        }

        return 0;
    }
}
```

Usage:
```bash
php nexus emails:send
php nexus emails:send --limit=50
```

### Generate Report Command

```php
<?php

namespace App\Commands;

use App\Models\User;
use App\Services\ReportGenerator;
use Nexus\Console\Command;

class GenerateReportCommand extends Command
{
    protected string $signature = 'report:generate {type} {--format=pdf} {--email=}';
    protected string $description = 'Generate application reports';

    public function __construct(
        protected ReportGenerator $generator
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = $this->argument('type');
        $format = $this->option('format');
        $email = $this->option('email');

        $this->info("Generating {$type} report in {$format} format...");

        try {
            $report = match($type) {
                'users' => $this->generateUsersReport(),
                'sales' => $this->generateSalesReport(),
                'analytics' => $this->generateAnalyticsReport(),
                default => throw new \InvalidArgumentException("Unknown report type: {$type}")
            };

            // Generate file
            $filename = "{$type}_report_" . date('Y-m-d') . ".{$format}";
            $filepath = storage_path("reports/{$filename}");

            $this->generator->generate($report, $filepath, $format);

            $this->success("Report generated: {$filename}");

            // Email if requested
            if ($email) {
                $this->line("Sending report to {$email}...");
                // Email sending logic
                $this->success("Report sent to {$email}");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Error generating report: {$e->getMessage()}");
            return 1;
        }
    }

    protected function generateUsersReport(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();

        return [
            'title' => 'Users Report',
            'data' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'inactive' => $totalUsers - $activeUsers
            ]
        ];
    }

    protected function generateSalesReport(): array
    {
        // Sales report logic
        return ['title' => 'Sales Report', 'data' => []];
    }

    protected function generateAnalyticsReport(): array
    {
        // Analytics report logic
        return ['title' => 'Analytics Report', 'data' => []];
    }
}
```

Usage:
```bash
php nexus report:generate users
php nexus report:generate sales --format=excel
php nexus report:generate analytics --format=csv --email=admin@example.com
```

### Database Cleanup Command

```php
<?php

namespace App\Commands;

use Nexus\Console\Command;
use Nexus\Database\Database;

class CleanupDatabaseCommand extends Command
{
    protected string $signature = 'db:cleanup {--days=30} {--tables=}';
    protected string $description = 'Clean up old database records';

    public function __construct(
        protected Database $db
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = $this->option('days');
        $tables = $this->option('tables');

        $this->warning("This will delete records older than {$days} days!");
        $this->line('');

        // Determine tables to clean
        $tablesToClean = $tables ? explode(',', $tables) : ['sessions', 'logs', 'temp_data'];

        $totalDeleted = 0;

        foreach ($tablesToClean as $table) {
            $this->info("Cleaning table: {$table}");

            $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

            $deleted = $this->db->table($table)
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            $this->line("Deleted {$deleted} records from {$table}");

            $totalDeleted += $deleted;
        }

        $this->line('');
        $this->success("Total records deleted: {$totalDeleted}");

        return 0;
    }
}
```

Usage:
```bash
php nexus db:cleanup
php nexus db:cleanup --days=60
php nexus db:cleanup --days=7 --tables=sessions,logs
```

### Import Data Command

```php
<?php

namespace App\Commands;

use App\Models\User;
use Nexus\Console\Command;

class ImportUsersCommand extends Command
{
    protected string $signature = 'import:users {file}';
    protected string $description = 'Import users from CSV file';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Importing users from {$file}...");
        $this->line('');

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);  // Skip header row

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            [$name, $email, $role] = $row;

            // Check if user exists
            $existing = User::where('email', $email)->first();

            if ($existing) {
                $this->warning("Skipping {$email} - already exists");
                $skipped++;
                continue;
            }

            // Create user
            User::create([
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'password' => password_hash('default', PASSWORD_BCRYPT)
            ]);

            $this->line("Imported: {$name} ({$email})");
            $imported++;
        }

        fclose($handle);

        $this->line('');
        $this->success("Imported {$imported} users");

        if ($skipped > 0) {
            $this->warning("Skipped {$skipped} existing users");
        }

        return 0;
    }
}
```

Usage:
```bash
php nexus import:users data/users.csv
```

## Best Practices

1. **Descriptive Names**: Use clear command names (verb:noun)
2. **Help Text**: Provide good descriptions
3. **Error Handling**: Handle errors gracefully
4. **Return Codes**: Return 0 for success, non-zero for failure
5. **User Feedback**: Use colored output methods
6. **Validation**: Validate arguments and options
7. **Logging**: Log command execution
8. **Testing**: Test commands thoroughly
9. **Documentation**: Document command usage
10. **Idempotent**: Commands should be safe to run multiple times

## Command Exit Codes

```php
// Success
return 0;

// General error
return 1;

// Specific error codes
return 2;  // Invalid argument
return 3;  // File not found
return 4;  // Permission denied
// etc.
```

## Next Steps

- Learn about [Artisan Commands](artisan-commands.md)
- Understand [Service Providers](service-providers.md)
- Explore [Dependency Injection](dependency-injection.md)
