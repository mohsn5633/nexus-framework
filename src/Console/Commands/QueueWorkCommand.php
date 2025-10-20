<?php

namespace Nexus\Console\Commands;

use Nexus\Console\Command;
use Nexus\Queue\QueueManager;

class QueueWorkCommand extends Command
{
    protected string $signature = 'queue:work {--queue=default} {--once} {--stop-when-empty}';
    protected string $description = 'Process jobs from the queue';

    public function handle(): int
    {
        $queue = $this->option('queue', 'default');
        $once = $this->option('once', false);
        $stopWhenEmpty = $this->option('stop-when-empty', false);

        $this->info("Processing jobs from '{$queue}' queue...");

        $queueManager = app(QueueManager::class);
        $connection = $queueManager->connection();
        $processed = 0;

        while (true) {
            $job = $connection->pop($queue);

            if ($job === null) {
                if ($once || $stopWhenEmpty) {
                    $this->info("No jobs to process.");
                    break;
                }

                // Wait before checking again
                sleep(3);
                continue;
            }

            try {
                $this->info("Processing job: {$job->getJobClass()}");

                // Execute the job
                $job->fire();

                // Delete the job
                $connection->delete($job->getId());

                $processed++;
                $this->success("Job completed successfully.");

                if ($once) {
                    break;
                }
            } catch (\Exception $e) {
                $this->error("Job failed: {$e->getMessage()}");

                // Mark job as failed
                $job->fail($e);

                if (method_exists($connection, 'failed')) {
                    $connection->failed($job->getId(), $e);
                }

                if ($once) {
                    return 1;
                }
            }
        }

        $this->info("Processed {$processed} job(s).");

        return 0;
    }
}
