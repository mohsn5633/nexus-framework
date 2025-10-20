<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;
use Nexus\Process\ProcessPool;
use Nexus\Process\Worker;

class WorkerRunCommand extends Command
{
    protected string $signature = 'worker:run {--workers=4} {--timeout=300}';
    protected string $description = 'Run worker pool for processing tasks';

    public function handle(): int
    {
        $maxWorkers = (int) ($this->option('workers') ?? config('process.pool.max_workers', 4));
        $timeout = (int) ($this->option('timeout') ?? config('process.pool.timeout', 300));

        $this->info("Starting worker pool with {$maxWorkers} workers");

        $pool = new ProcessPool($maxWorkers, $timeout);

        // Example: Add some tasks
        // In a real application, you would fetch tasks from a queue

        $this->info("Worker pool is ready and waiting for tasks...");
        $this->info("Press Ctrl+C to stop");

        // Keep the worker pool running
        while (true) {
            sleep(1);

            // You can add logic here to:
            // - Fetch tasks from a queue
            // - Process tasks
            // - Monitor worker health
        }

        return 0;
    }
}
