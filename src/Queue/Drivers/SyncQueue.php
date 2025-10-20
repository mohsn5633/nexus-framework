<?php

namespace Nexus\Queue\Drivers;

use Nexus\Queue\QueueInterface;
use Nexus\Queue\Job;

/**
 * Sync Queue Driver
 *
 * Executes jobs immediately (no actual queuing)
 */
class SyncQueue implements QueueInterface
{
    /**
     * Push a job onto the queue (execute immediately)
     */
    public function push(string $job, array $data = [], ?string $queue = null): mixed
    {
        $jobInstance = new $job();

        if (method_exists($jobInstance, 'handle')) {
            $jobInstance->handle(...array_values($data));
        }

        return true;
    }

    /**
     * Push a job with delay (ignore delay in sync mode)
     */
    public function later(int $delay, string $job, array $data = [], ?string $queue = null): mixed
    {
        return $this->push($job, $data, $queue);
    }

    /**
     * Pop a job (not applicable for sync queue)
     */
    public function pop(?string $queue = null): ?Job
    {
        return null;
    }

    /**
     * Get queue size (always 0 for sync)
     */
    public function size(?string $queue = null): int
    {
        return 0;
    }

    /**
     * Delete a job (not applicable for sync)
     */
    public function delete(mixed $id): bool
    {
        return true;
    }
}
