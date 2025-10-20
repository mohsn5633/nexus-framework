# Task Scheduling

The Nexus Scheduler allows you to fluently and expressively define your command schedule within your application itself, requiring only a single cron entry on your server.

## Table of Contents

- [Introduction](#introduction)
- [Defining Schedules](#defining-schedules)
- [Schedule Frequency](#schedule-frequency)
- [Running the Scheduler](#running-the-scheduler)
- [Examples](#examples)

## Introduction

The task scheduler provides a cron-like interface for scheduling tasks to run at specific intervals without needing to set up multiple cron jobs.

### Features

- **Fluent API**: Easy-to-read scheduling syntax
- **Multiple Frequencies**: Minute, hourly, daily, weekly, monthly
- **No Cron Clutter**: One cron entry runs all tasks
- **Prevent Overlapping**: Ensure tasks don't run concurrently
- **Job Scheduling**: Schedule background jobs
- **Command Scheduling**: Schedule console commands

## Defining Schedules

All scheduled tasks are defined in `app/Console/Schedule.php`:

```php
<?php

use Nexus\Schedule\Scheduler;

function schedule(Scheduler $schedule): void
{
    // Define your scheduled tasks here
}
```

## Schedule Frequency

### Common Frequencies

```php
// Every minute
$schedule->call(fn() => /* task */)
    ->everyMinute();

// Every five minutes
$schedule->call(fn() => /* task */)
    ->everyFiveMinutes();

// Every ten minutes
$schedule->call(fn() => /* task */)
    ->everyTenMinutes();

// Every fifteen minutes
$schedule->call(fn() => /* task */)
    ->everyFifteenMinutes();

// Every thirty minutes
$schedule->call(fn() => /* task */)
    ->everyThirtyMinutes();

// Hourly
$schedule->call(fn() => /* task */)
    ->hourly();

// Hourly at specific minute
$schedule->call(fn() => /* task */)
    ->hourlyAt(15); // 15 minutes past each hour

// Daily at midnight
$schedule->call(fn() => /* task */)
    ->daily();

// Daily at specific time
$schedule->call(fn() => /* task */)
    ->dailyAt('13:00');

// Weekly (Sunday at midnight)
$schedule->call(fn() => /* task */)
    ->weekly();

// Monthly (1st day at midnight)
$schedule->call(fn() => /* task */)
    ->monthly();

// Yearly (January 1st at midnight)
$schedule->call(fn() => /* task */)
    ->yearly();
```

### Day Constraints

```php
// Weekdays only (Monday-Friday)
$schedule->call(fn() => /* task */)
    ->weekdays();

// Weekends only (Saturday-Sunday)
$schedule->call(fn() => /* task */)
    ->weekends();

// Specific days
$schedule->call(fn() => /* task */)
    ->mondays();

$schedule->call(fn() => /* task */)
    ->tuesdays();

$schedule->call(fn() => /* task */)
    ->fridays();
```

### Custom Cron Expressions

```php
// Custom cron expression
$schedule->call(fn() => /* task */)
    ->cron('0 */4 * * *'); // Every 4 hours
```

Cron format: `minute hour day month day-of-week`

## Running the Scheduler

### Setup Cron Entry

Add a single cron entry to run the scheduler every minute:

```bash
* * * * * cd /path/to/nexus && php nexus schedule:run >> /dev/null 2>&1
```

This single cron entry will call the scheduler every minute, which will evaluate your scheduled tasks and run the ones that are due.

### Manual Execution

```bash
# Run scheduled tasks
php nexus schedule:run

# List scheduled tasks
php nexus schedule:list
```

## Task Types

### Schedule Closures

```php
$schedule->call(function () {
    // Clean up temporary files
    $files = glob(storage_path('temp/*'));
    foreach ($files as $file) {
        if (is_file($file) && time() - filemtime($file) > 86400) {
            unlink($file);
        }
    }
})->daily()->description('Clean up old temporary files');
```

### Schedule Jobs

```php
use App\Jobs\ProcessReportsJob;

$schedule->job(ProcessReportsJob::class)
    ->everyFiveMinutes()
    ->description('Process pending reports');

// With job arguments
$schedule->job(ProcessReportsJob::class, [$userId, $reportType])
    ->hourly()
    ->description('Generate user reports');
```

### Schedule Commands

```php
// Run artisan commands
$schedule->command('emails:send')
    ->dailyAt('08:00')
    ->description('Send daily email digest');

$schedule->command('backup:database')
    ->daily()
    ->description('Backup database');
```

### Schedule Shell Commands

```php
$schedule->exec('php /path/to/script.php')
    ->hourly()
    ->description('Run external script');

$schedule->exec('cp -r /source /backup')
    ->daily()
    ->description('Copy files to backup location');
```

## Prevent Overlapping

Prevent tasks from running if the previous instance is still executing:

```php
$schedule->job(SyncDataJob::class)
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->description('Sync data with external API');
```

This is useful for long-running tasks that shouldn't run concurrently.

## Examples

### Daily Database Backup

```php
function schedule(Scheduler $schedule): void
{
    $schedule->exec('mysqldump -u root -p dbname > /backup/db_$(date +%Y%m%d).sql')
        ->dailyAt('02:00')
        ->description('Daily database backup');
}
```

### Clean Up Old Logs

```php
function schedule(Scheduler $schedule): void
{
    $schedule->call(function () {
        $logPath = storage_path('logs');
        $files = glob("{$logPath}/*.log");

        foreach ($files as $file) {
            // Delete logs older than 30 days
            if (time() - filemtime($file) > 30 * 86400) {
                unlink($file);
            }
        }
    })->weekly()->description('Clean up old log files');
}
```

### Send Weekly Reports

```php
use App\Jobs\SendWeeklyReportJob;

function schedule(Scheduler $schedule): void
{
    $schedule->job(SendWeeklyReportJob::class)
        ->weekly()
        ->description('Send weekly reports to users');
}
```

### Process Queue Every Minute

```php
function schedule(Scheduler $schedule): void
{
    $schedule->command('queue:work --once')
        ->everyMinute()
        ->withoutOverlapping()
        ->description('Process queued jobs');
}
```

### Sync Data from API

```php
use App\Jobs\SyncProductsJob;

function schedule(Scheduler $schedule): void
{
    $schedule->job(SyncProductsJob::class)
        ->everyFifteenMinutes()
        ->withoutOverlapping()
        ->description('Sync products from external API');
}
```

### Generate Sitemap

```php
use App\Jobs\GenerateSitemapJob;

function schedule(Scheduler $schedule): void
{
    $schedule->job(GenerateSitemapJob::class)
        ->daily()
        ->description('Generate sitemap.xml');
}
```

### Cache Warming

```php
function schedule(Scheduler $schedule): void
{
    $schedule->call(function () {
        // Warm up cache with popular content
        $db = app(\Nexus\Database\Database::class);
        $cache = app(\Nexus\Cache\CacheManager::class);

        $popular = $db->table('posts')
            ->where('views', '>', 1000)
            ->get();

        foreach ($popular as $post) {
            $cache->put("post:{$post['id']}", $post, 3600);
        }
    })->hourly()->description('Warm up cache with popular posts');
}
```

### Send Birthday Emails

```php
use App\Jobs\SendBirthdayEmailsJob;

function schedule(Scheduler $schedule): void
{
    $schedule->job(SendBirthdayEmailsJob::class)
        ->dailyAt('09:00')
        ->description('Send birthday emails to users');
}
```

### Comprehensive Schedule Example

```php
<?php

use Nexus\Schedule\Scheduler;
use App\Jobs\{
    ProcessReportsJob,
    SendEmailDigestJob,
    CleanupTempFilesJob,
    BackupDatabaseJob,
    SyncInventoryJob
};

function schedule(Scheduler $schedule): void
{
    // Every minute
    $schedule->job(ProcessReportsJob::class)
        ->everyMinute()
        ->withoutOverlapping()
        ->description('Process pending reports');

    // Every 5 minutes
    $schedule->job(SyncInventoryJob::class)
        ->everyFiveMinutes()
        ->withoutOverlapping()
        ->description('Sync inventory with warehouse');

    // Hourly
    $schedule->call(function () {
        // Clear expired sessions
        $db = app(\Nexus\Database\Database::class);
        $db->table('sessions')
            ->where('last_activity', '<', time() - 7200)
            ->delete();
    })->hourly()->description('Clean up expired sessions');

    // Daily at specific time
    $schedule->job(SendEmailDigestJob::class)
        ->dailyAt('08:00')
        ->description('Send daily email digest');

    $schedule->job(BackupDatabaseJob::class)
        ->dailyAt('02:00')
        ->description('Daily database backup');

    // Weekly
    $schedule->job(CleanupTempFilesJob::class)
        ->weekly()
        ->description('Clean up temporary files');

    // Monthly
    $schedule->call(function () {
        // Generate monthly reports
        $db = app(\Nexus\Database\Database::class);

        $stats = $db->table('orders')
            ->where('created_at', '>=', now()->startOfMonth()->toDateTimeString())
            ->get();

        // Process and save report
        file_put_contents(
            storage_path('reports/monthly_' . now()->format('Y_m') . '.json'),
            json_encode($stats)
        );
    })->monthly()->description('Generate monthly sales report');

    // Weekdays only
    $schedule->call(function () {
        // Business day tasks
    })->weekdays()->dailyAt('09:00')->description('Business day startup tasks');

    // Custom cron expression (every 4 hours)
    $schedule->command('cache:clear')
        ->cron('0 */4 * * *')
        ->description('Clear cache every 4 hours');
}
```

## Best Practices

1. **Use Descriptions**: Always add descriptions to tasks
2. **Prevent Overlapping**: Use for long-running tasks
3. **Monitor Execution**: Log task execution
4. **Test Schedules**: Test tasks independently
5. **Use Jobs**: Prefer jobs over closures for complex logic
6. **Set Appropriate Times**: Consider server load
7. **Handle Failures**: Implement error handling
8. **Document Schedules**: Comment complex cron expressions

## Troubleshooting

### Tasks Not Running

1. Verify cron entry is correct
2. Check cron is actually executing
3. Review scheduler logs
4. Test with `php nexus schedule:run`

### Tasks Running Multiple Times

1. Check for multiple cron entries
2. Verify `withoutOverlapping()` is used
3. Check server time synchronization

### Wrong Execution Times

1. Verify server timezone
2. Check cron expression syntax
3. Test with `php nexus schedule:list`

## Cron Expression Reference

```
* * * * *
│ │ │ │ │
│ │ │ │ └─ Day of week (0-7, Sunday = 0 or 7)
│ │ │ └─── Month (1-12)
│ │ └───── Day of month (1-31)
│ └─────── Hour (0-23)
└───────── Minute (0-59)
```

**Examples:**
- `0 * * * *` - Every hour at minute 0
- `0 0 * * *` - Daily at midnight
- `0 0 * * 0` - Weekly (Sunday at midnight)
- `0 0 1 * *` - Monthly (1st at midnight)
- `*/5 * * * *` - Every 5 minutes
- `0 */2 * * *` - Every 2 hours
- `0 9 * * 1-5` - Weekdays at 9 AM

## Next Steps

- Learn about [Queues and Jobs](queues.md)
- Understand [Commands](creating-commands.md)
- Explore [Cache](cache.md)
