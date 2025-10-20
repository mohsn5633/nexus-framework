<?php

use Nexus\Schedule\Scheduler;

/**
 * Define the application's scheduled tasks
 *
 * @param Scheduler $schedule
 * @return void
 */
function schedule(Scheduler $schedule): void
{
    // Example: Run a job every minute
    // $schedule->job(App\Jobs\ProcessReports::class)
    //     ->everyMinute()
    //     ->description('Process pending reports');

    // Example: Run a closure every hour
    // $schedule->call(function () {
    //     // Your code here
    // })->hourly()->description('Clean up temporary files');

    // Example: Run an artisan command daily
    // $schedule->command('emails:send')
    //     ->dailyAt('08:00')
    //     ->description('Send daily email digest');

    // Example: Run a shell command weekly
    // $schedule->exec('php /path/to/script.php')
    //     ->weekly()
    //     ->description('Weekly backup');

    // Example: Prevent overlapping tasks
    // $schedule->job(App\Jobs\SyncData::class)
    //     ->everyFiveMinutes()
    //     ->withoutOverlapping()
    //     ->description('Sync data with external API');
}
