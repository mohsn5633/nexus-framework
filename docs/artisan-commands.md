# Artisan Commands

Nexus Framework includes a powerful command-line interface called Artisan, which provides helpful commands for development.

## Available Commands

View all available commands:

```bash
php nexus list
```

## Development Server

### serve

Start the built-in development server:

```bash
# Start on default port (8000)
php nexus serve

# Start on custom port
php nexus serve --port=8080

# Start on specific host
php nexus serve --host=0.0.0.0
```

Visit `http://localhost:8000` to see your application.

## Code Generation

### make:controller

Generate a new controller:

```bash
# Basic controller
php nexus make:controller UserController

# Resource controller (with CRUD methods)
php nexus make:controller PostController --resource

# API controller
php nexus make:controller ApiController --resource
```

Generated controllers are placed in `app/Controllers/`.

### make:model

Generate a new model:

```bash
# Basic model
php nexus make:model User

# Model with custom table
php nexus make:model Product --table=products_catalog
```

Generated models are placed in `app/Models/`.

### make:module

Generate a complete module (model + resource controller):

```bash
php nexus make:module Product
```

This creates:
- `app/Models/Product.php`
- `app/Controllers/ProductController.php` (resource controller)

### make:middleware

Generate a new middleware:

```bash
php nexus make:middleware AuthMiddleware
php nexus make:middleware CorsMiddleware
```

Generated middleware is placed in `app/Middleware/`.

### make:validation

Generate a validation class:

```bash
php nexus make:validation UserStoreValidation
php nexus make:validation ProductUpdateValidation
```

Generated validation classes are placed in `app/Validations/`.

### make:provider

Generate a service provider:

```bash
php nexus make:provider PaymentServiceProvider
php nexus make:provider CacheServiceProvider
```

Generated providers are placed in `app/Providers/`.

### make:command

Generate a custom CLI command:

```bash
php nexus make:command SendEmails
php nexus make:command GenerateReport
```

Generated commands are placed in `app/Commands/`.

### make:package

Generate a package structure:

```bash
php nexus make:package BlogEngine
php nexus make:package PaymentGateway
```

Generated packages are placed in `packages/`.

## Routing

### routes:list

Display all registered routes:

```bash
php nexus routes:list
```

Output:
```
+--------+-------------------------+-------------------+------------------+
| Method | URI                     | Name              | Middleware       |
+--------+-------------------------+-------------------+------------------+
| GET    | /                       | home              |                  |
| GET    | /users                  | users.index       | auth             |
| POST   | /users                  | users.store       | auth, csrf       |
| GET    | /users/{id}             | users.show        |                  |
| PUT    | /users/{id}             | users.update      | auth             |
| DELETE | /users/{id}             | users.destroy     | auth, admin      |
+--------+-------------------------+-------------------+------------------+
```

## Storage

### storage:link

Create symbolic link from `public/storage` to `storage/app/public`:

```bash
php nexus storage:link
```

This allows public access to files stored in the `public` disk.

On Windows, you may need to run as Administrator.

## Maintenance Mode

### down

Put application into maintenance mode:

```bash
php nexus down
```

This creates a `storage/framework/down` file and displays a maintenance page to visitors.

A secret bypass key is generated:
```
Application is now in maintenance mode.

Bypass URL: http://yourapp.com?secret=abc123xyz
Secret key: abc123xyz
```

Use the secret key to access the application during maintenance:
```
http://yourapp.com?secret=abc123xyz
```

The secret is stored in your session, so you only need to use it once.

### up

Bring application out of maintenance mode:

```bash
php nexus up
```

This removes the maintenance mode file.

## Cache & Views

### view:clear

Clear compiled Blade views:

```bash
php nexus view:clear
```

This deletes all compiled views from `storage/framework/views/`.

Useful when:
- Views aren't updating
- Template errors occur
- After deployment

## Creating Custom Commands

### Generate Command

```bash
php nexus make:command SendEmails
```

### Command Structure

```php
<?php

namespace App\Commands;

use Nexus\Console\Command;

class SendEmailsCommand extends Command
{
    protected string $signature = 'emails:send';
    protected string $description = 'Send pending emails';

    public function handle(): int
    {
        $this->info('Sending emails...');

        // Your email sending logic here
        $sent = 10;

        $this->success("Successfully sent {$sent} emails!");

        return 0;
    }
}
```

### Register Custom Command

Add to `src/Console/Kernel.php`:

```php
protected function registerDefaultCommands(): void
{
    $this->commands = [
        // ... existing commands
        'emails:send' => \App\Commands\SendEmailsCommand::class,
    ];
}
```

Run your command:

```bash
php nexus emails:send
```

## Command Output Methods

Use these methods in your command classes:

### line()

Output regular text:

```php
$this->line('Processing...');
```

### info()

Output info message (cyan):

```php
$this->info('Processing completed.');
```

### success()

Output success message (green):

```php
$this->success('Task completed successfully!');
```

### warning()

Output warning message (yellow):

```php
$this->warning('This action cannot be undone.');
```

### error()

Output error message (red):

```php
$this->error('An error occurred!');
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

## Command Options

### Defining Options

```php
protected string $signature = 'user:list';

public function handle(): int
{
    $active = $this->option('active');

    if ($active) {
        $this->info('Listing active users only');
        // List active users
    } else {
        $this->info('Listing all users');
        // List all users
    }

    return 0;
}
```

Usage:
```bash
php nexus user:list --active
```

### Option with Value

```php
protected string $signature = 'user:list';

public function handle(): int
{
    $limit = $this->option('limit') ?? 10;

    $this->info("Showing {$limit} users");

    return 0;
}
```

Usage:
```bash
php nexus user:list --limit=50
```

## Complete Command Example

```php
<?php

namespace App\Commands;

use Nexus\Console\Command;
use App\Models\User;

class GenerateReportCommand extends Command
{
    protected string $signature = 'report:generate {type} {--format=pdf}';
    protected string $description = 'Generate application reports';

    public function handle(): int
    {
        $type = $this->argument('type');
        $format = $this->option('format');

        $this->info("Generating {$type} report in {$format} format...");

        try {
            switch ($type) {
                case 'users':
                    $this->generateUsersReport($format);
                    break;
                case 'sales':
                    $this->generateSalesReport($format);
                    break;
                default:
                    $this->error("Unknown report type: {$type}");
                    return 1;
            }

            $this->success('Report generated successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error("Error generating report: {$e->getMessage()}");
            return 1;
        }
    }

    protected function generateUsersReport(string $format): void
    {
        $users = User::all();
        $this->info("Found " . count($users) . " users");

        // Generate report logic
        $filename = "users_report." . $format;
        $this->line("Report saved: {$filename}");
    }

    protected function generateSalesReport(string $format): void
    {
        // Sales report logic
        $this->line("Generating sales report...");
    }
}
```

Usage:
```bash
php nexus report:generate users
php nexus report:generate users --format=csv
php nexus report:generate sales --format=xlsx
```

## Best Practices

1. **Descriptive Names**: Use clear command names (verb:noun)
2. **Help Text**: Provide good descriptions
3. **Error Handling**: Handle errors gracefully
4. **Return Codes**: Return 0 for success, non-zero for failure
5. **Output Feedback**: Use colored output methods
6. **Validation**: Validate arguments and options
7. **Logging**: Log command execution
8. **Testing**: Test commands thoroughly

## Quick Reference

```bash
# Development
php nexus serve                    # Start dev server
php nexus routes:list              # List all routes

# Code Generation
php nexus make:controller Name     # Create controller
php nexus make:model Name          # Create model
php nexus make:middleware Name     # Create middleware
php nexus make:module Name         # Create module
php nexus make:validation Name     # Create validation
php nexus make:provider Name       # Create provider
php nexus make:command Name        # Create command

# Storage
php nexus storage:link             # Create storage symlink

# Maintenance
php nexus down                     # Enable maintenance mode
php nexus up                       # Disable maintenance mode

# Cache
php nexus view:clear               # Clear view cache

# Help
php nexus list                     # Show all commands
php nexus help command-name        # Show command help
```

## Next Steps

- Learn about [Creating Commands](creating-commands.md)
- Understand [Package Development](package-development.md)
- Explore [Controllers](controllers.md)
