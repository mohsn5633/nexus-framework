<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;
use Nexus\Schedule\Scheduler;

class ScheduleRunCommand extends Command
{
    protected string $signature = 'schedule:run';
    protected string $description = 'Run the scheduled commands';

    public function handle(): int
    {
        $scheduler = new Scheduler();

        // Load user-defined schedule
        $schedulePath = base_path('app/Console/Schedule.php');
        if (file_exists($schedulePath)) {
            require $schedulePath;
            if (function_exists('schedule')) {
                schedule($scheduler);
            }
        }

        $dueEvents = $scheduler->dueEvents();

        if (empty($dueEvents)) {
            $this->info('No scheduled tasks are due to run.');
            return 0;
        }

        $this->info('Running ' . count($dueEvents) . ' scheduled task(s)...');

        foreach ($dueEvents as $event) {
            $description = $event->getDescription() ?: 'Scheduled task';

            $this->info("Running: {$description}");

            try {
                $event->run();
                $this->success("Completed: {$description}");
            } catch (\Exception $e) {
                $this->error("Failed: {$description} - {$e->getMessage()}");
            }
        }

        return 0;
    }
}
