<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;
use Nexus\Schedule\Scheduler;

class ScheduleListCommand extends Command
{
    protected string $signature = 'schedule:list';
    protected string $description = 'List all scheduled tasks';

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

        $events = $scheduler->events();

        if (empty($events)) {
            $this->info('No scheduled tasks defined.');
            return 0;
        }

        $this->info('Scheduled Tasks:');
        $this->line('');

        // Header
        $this->line(sprintf(
            '%-40s | %-20s | %s',
            'Description',
            'Cron Expression',
            'Next Due'
        ));
        $this->line(str_repeat('-', 80));

        foreach ($events as $event) {
            $description = $event->getDescription() ?: 'Unnamed task';
            $expression = $event->getExpression();
            $isDue = $event->isDue() ? 'Now' : 'Later';

            $this->line(sprintf(
                '%-40s | %-20s | %s',
                substr($description, 0, 40),
                $expression,
                $isDue
            ));
        }

        return 0;
    }
}
