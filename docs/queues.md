# Queues and Jobs

Queues allow you to defer time-consuming tasks, such as sending emails or processing files, to be processed in the background, improving your application's response time.

## Table of Contents

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Creating Jobs](#creating-jobs)
- [Dispatching Jobs](#dispatching-jobs)
- [Running Queue Workers](#running-queue-workers)
- [Queue Drivers](#queue-drivers)
- [Examples](#examples)

## Introduction

Queues provide a way to defer time-consuming tasks to be processed asynchronously in the background, allowing your application to respond faster to users.

### Features

- **Multiple Drivers**: Sync, Database, Redis
- **Job Dispatching**: Easy job dispatch with helpers
- **Delayed Jobs**: Schedule jobs to run after a delay
- **Multiple Queues**: Organize jobs by priority or type
- **Failed Job Handling**: Automatic failed job tracking
- **Job Retries**: Configurable retry behavior

## Configuration

### Environment Variables

Configure queue settings in `.env`:

```env
QUEUE_CONNECTION=database
QUEUE_NAME=default
QUEUE_TABLE=jobs
QUEUE_RETRY_AFTER=90

# Redis configuration (if using redis driver)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_QUEUE_DB=0
```

### Configuration File

Queue configuration is in `config/queue.php`:

```php
return [
    'default' => env('QUEUE_CONNECTION', 'sync'),

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => '127.0.0.1',
            'port' => 6379,
            'queue' => 'default',
        ],
    ],
];
```

### Database Setup

If using the database driver, create the queue tables:

```bash
# Generate migration
php nexus queue:table

# Run migration
php nexus migrate
```

## Creating Jobs

### Using CLI

```bash
php nexus make:job SendEmailJob
php nexus make:job ProcessVideoJob
php nexus make:job GenerateReportJob
```

This creates a job class in `app/Jobs/`:

```php
<?php

namespace App\Jobs;

use Nexus\Queue\Dispatchable;

class SendEmailJob
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
    public function failed(\Exception $exception): void
    {
        // Handle failure
    }
}
```

### Job Structure

A typical job class:

```php
<?php

namespace App\Jobs;

use Nexus\Queue\Dispatchable;
use App\Models\User;

class SendWelcomeEmailJob
{
    use Dispatchable;

    protected int $userId;
    protected string $emailType;

    public function __construct(int $userId, string $emailType = 'welcome')
    {
        $this->userId = $userId;
        $this->emailType = $emailType;
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            return;
        }

        // Send email
        mail(
            $user['email'],
            'Welcome to Our Platform',
            "Hello {$user['name']}, welcome to our platform!"
        );
    }

    /**
     * Handle job failure
     */
    public function failed(\Exception $exception): void
    {
        // Log the error
        error_log("Failed to send email to user {$this->userId}: {$exception->getMessage()}");

        // Notify administrators
        // ...
    }
}
```

## Dispatching Jobs

### Using Static Method

```php
use App\Jobs\SendWelcomeEmailJob;

// Dispatch immediately
SendWelcomeEmailJob::dispatch($userId, 'welcome');

// Dispatch after 5 minutes (300 seconds)
SendWelcomeEmailJob::dispatchAfter(300, $userId, 'welcome');

// Dispatch to specific queue
SendWelcomeEmailJob::dispatchOn('emails', $userId, 'welcome');
```

### Using Helper Functions

```php
// Dispatch job
dispatch(SendWelcomeEmailJob::class, [$userId, 'welcome']);

// Dispatch after delay
dispatch_after(300, SendWelcomeEmailJob::class, [$userId, 'welcome']);

// Dispatch to specific queue
dispatch(SendWelcomeEmailJob::class, [$userId, 'welcome'], 'emails');
```

### Using Queue Manager

```php
use Nexus\Queue\QueueManager;

class UserController
{
    public function register(Request $request, QueueManager $queue)
    {
        // Create user...

        // Dispatch job
        $queue->push(SendWelcomeEmailJob::class, [$userId]);

        return Response::json(['success' => true]);
    }
}
```

## Running Queue Workers

### Process Jobs

```bash
# Start queue worker
php nexus queue:work

# Process from specific queue
php nexus queue:work --queue=emails

# Process one job and stop
php nexus queue:work --once

# Stop when queue is empty
php nexus queue:work --stop-when-empty
```

### Production

For production, use a process monitor like Supervisor:

**supervisord.conf:**
```ini
[program:nexus-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/nexus queue:work
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/path/to/nexus/storage/logs/worker.log
stopwaitsecs=3600
```

## Queue Drivers

### Sync Driver

Executes jobs immediately (no actual queuing):

```php
'connections' => [
    'sync' => [
        'driver' => 'sync',
    ],
],
```

**Use Case**: Local development, testing

### Database Driver

Stores jobs in database:

```php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
    ],
],
```

**Use Case**: Simple setup, no additional services required

### Redis Driver

Stores jobs in Redis (fastest):

```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
        'queue' => 'default',
    ],
],
```

**Use Case**: High-performance production applications

## Examples

### Send Email Job

```php
<?php

namespace App\Jobs;

use Nexus\Queue\Dispatchable;

class SendEmailJob
{
    use Dispatchable;

    public function __construct(
        protected string $to,
        protected string $subject,
        protected string $message
    ) {}

    public function handle(): void
    {
        mail($this->to, $this->subject, $this->message);
    }

    public function failed(\Exception $e): void
    {
        error_log("Failed to send email to {$this->to}: {$e->getMessage()}");
    }
}

// Usage
SendEmailJob::dispatch('user@example.com', 'Hello', 'Welcome!');
```

### Process Video Job

```php
<?php

namespace App\Jobs;

use Nexus\Queue\Dispatchable;

class ProcessVideoJob
{
    use Dispatchable;

    public function __construct(
        protected string $videoPath,
        protected string $outputPath
    ) {}

    public function handle(): void
    {
        // Process video (this can take minutes)
        exec("ffmpeg -i {$this->videoPath} -vcodec h264 {$this->outputPath}");

        // Update database
        // Notify user
    }

    public function failed(\Exception $e): void
    {
        // Clean up temporary files
        @unlink($this->videoPath);

        // Notify user of failure
    }
}

// Usage
ProcessVideoJob::dispatch($inputPath, $outputPath);
```

### Generate Report Job

```php
<?php

namespace App\Jobs;

use Nexus\Queue\Dispatchable;
use Nexus\Database\Database;

class GenerateMonthlyReportJob
{
    use Dispatchable;

    public function __construct(
        protected int $userId,
        protected int $month,
        protected int $year
    ) {}

    public function handle(Database $db): void
    {
        // Generate complex report
        $data = $db->table('orders')
            ->where('user_id', $this->userId)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get();

        // Process data
        $report = $this->processData($data);

        // Save to file
        $filename = "report_{$this->userId}_{$this->year}_{$this->month}.pdf";
        file_put_contents(storage_path("reports/{$filename}"), $report);

        // Notify user
        SendEmailJob::dispatch(
            $this->getUserEmail($this->userId),
            'Your Monthly Report',
            "Your report is ready: {$filename}"
        );
    }

    protected function processData(array $data): string
    {
        // Generate PDF or other format
        return 'Report content';
    }

    protected function getUserEmail(int $userId): string
    {
        // Get user email from database
        return 'user@example.com';
    }
}

// Usage
GenerateMonthlyReportJob::dispatch($userId, 1, 2025);
```

### Bulk Import Job

```php
<?php

namespace App\Jobs;

use Nexus\Queue\Dispatchable;
use Nexus\Database\Database;

class ImportUsersJob
{
    use Dispatchable;

    public function __construct(
        protected string $csvPath
    ) {}

    public function handle(Database $db): void
    {
        $file = fopen($this->csvPath, 'r');

        // Skip header
        fgetcsv($file);

        $imported = 0;

        while (($row = fgetcsv($file)) !== false) {
            [$name, $email, $phone] = $row;

            // Validate and import
            if ($this->isValidEmail($email)) {
                $db->table('users')->insert([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'created_at' => now()->toDateTimeString(),
                ]);

                $imported++;
            }
        }

        fclose($file);

        // Clean up
        @unlink($this->csvPath);

        // Notify admin
        error_log("Imported {$imported} users");
    }

    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

// Usage
ImportUsersJob::dispatch($uploadedCsvPath);
```

### Image Optimization Job

```php
<?php

namespace App\Jobs;

use Nexus\Queue\Dispatchable;

class OptimizeImageJob
{
    use Dispatchable;

    public function __construct(
        protected string $imagePath
    ) {}

    public function handle(): void
    {
        // Load image
        $image = imagecreatefromjpeg($this->imagePath);

        // Resize
        $resized = imagescale($image, 800);

        // Save with compression
        imagejpeg($resized, $this->imagePath, 85);

        // Clean up
        imagedestroy($image);
        imagedestroy($resized);
    }
}

// Usage
OptimizeImageJob::dispatch($uploadedImagePath);
```

## Best Practices

1. **Keep Jobs Simple**: One job should do one thing
2. **Make Jobs Idempotent**: Safe to run multiple times
3. **Handle Failures**: Implement the `failed()` method
4. **Use Delays Wisely**: Don't overload queues with delayed jobs
5. **Monitor Queue Size**: Track pending jobs
6. **Choose Right Driver**: Use Redis for production
7. **Chunk Large Tasks**: Break into smaller jobs
8. **Clean Up Resources**: Remove temporary files in failed()
9. **Log Everything**: Track job execution
10. **Test Jobs**: Unit test job logic

## Troubleshooting

### Jobs Not Processing

1. Check if queue worker is running
2. Verify database/Redis connection
3. Check job table for pending jobs
4. Review worker logs

### Jobs Failing

1. Check failed_jobs table
2. Review exception messages
3. Test job logic independently
4. Check resource availability

### Performance Issues

1. Increase number of workers
2. Use Redis instead of database
3. Add indexes to jobs table
4. Monitor queue depth

## Next Steps

- Learn about [Task Scheduling](scheduler.md)
- Understand [Cache](cache.md)
- Explore [Database](database.md)
