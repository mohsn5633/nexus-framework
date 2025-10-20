<?php

namespace Nexus\Queue\Drivers;

use Nexus\Queue\QueueInterface;
use Nexus\Queue\Job;
use Redis;

/**
 * Redis Queue Driver
 *
 * Stores jobs in Redis using lists
 */
class RedisQueue implements QueueInterface
{
    protected Redis $redis;
    protected string $defaultQueue;
    protected string $prefix;

    public function __construct(array $config = [])
    {
        $this->redis = new Redis();
        $this->redis->connect(
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? 6379
        );

        if (!empty($config['password'])) {
            $this->redis->auth($config['password']);
        }

        if (isset($config['database'])) {
            $this->redis->select($config['database']);
        }

        $this->defaultQueue = $config['queue'] ?? 'default';
        $this->prefix = $config['prefix'] ?? 'queues:';
    }

    /**
     * Push a job onto the queue
     */
    public function push(string $job, array $data = [], ?string $queue = null): mixed
    {
        $queue = $queue ?? $this->defaultQueue;
        $payload = $this->createPayload($job, $data);

        return $this->redis->rPush($this->getQueueKey($queue), $payload);
    }

    /**
     * Push a job with delay
     */
    public function later(int $delay, string $job, array $data = [], ?string $queue = null): mixed
    {
        $queue = $queue ?? $this->defaultQueue;
        $payload = $this->createPayload($job, $data);
        $availableAt = time() + $delay;

        return $this->redis->zAdd(
            $this->getDelayedKey($queue),
            $availableAt,
            $payload
        );
    }

    /**
     * Pop the next job off the queue
     */
    public function pop(?string $queue = null): ?Job
    {
        $queue = $queue ?? $this->defaultQueue;

        // Move delayed jobs that are ready
        $this->migrateDelayedJobs($queue);

        // Pop job from queue
        $payload = $this->redis->lPop($this->getQueueKey($queue));

        if (!$payload) {
            return null;
        }

        $data = json_decode($payload, true);

        // Generate unique ID for this job
        $jobId = uniqid('job_', true);

        // Store in processing set
        $this->redis->hSet(
            $this->getProcessingKey($queue),
            $jobId,
            json_encode([
                'payload' => $payload,
                'reserved_at' => time(),
                'attempts' => 1,
            ])
        );

        return new Job(
            $jobId,
            $queue,
            $data['job'],
            $data['data'],
            1
        );
    }

    /**
     * Get queue size
     */
    public function size(?string $queue = null): int
    {
        $queue = $queue ?? $this->defaultQueue;
        return $this->redis->lLen($this->getQueueKey($queue));
    }

    /**
     * Delete a job
     */
    public function delete(mixed $id): bool
    {
        // Remove from processing set
        return $this->redis->hDel($this->getProcessingKey($this->defaultQueue), $id) > 0;
    }

    /**
     * Release a job back to the queue
     */
    public function release(mixed $id, int $delay = 0, ?string $queue = null): bool
    {
        $queue = $queue ?? $this->defaultQueue;

        // Get job from processing set
        $jobData = $this->redis->hGet($this->getProcessingKey($queue), $id);

        if (!$jobData) {
            return false;
        }

        $data = json_decode($jobData, true);
        $payload = $data['payload'];

        // Remove from processing
        $this->redis->hDel($this->getProcessingKey($queue), $id);

        // Push back to queue
        if ($delay > 0) {
            $availableAt = time() + $delay;
            $this->redis->zAdd($this->getDelayedKey($queue), $availableAt, $payload);
        } else {
            $this->redis->rPush($this->getQueueKey($queue), $payload);
        }

        return true;
    }

    /**
     * Create job payload
     */
    protected function createPayload(string $job, array $data): string
    {
        return json_encode([
            'job' => $job,
            'data' => $data,
        ]);
    }

    /**
     * Get queue key
     */
    protected function getQueueKey(string $queue): string
    {
        return $this->prefix . $queue;
    }

    /**
     * Get delayed jobs key
     */
    protected function getDelayedKey(string $queue): string
    {
        return $this->prefix . $queue . ':delayed';
    }

    /**
     * Get processing jobs key
     */
    protected function getProcessingKey(string $queue): string
    {
        return $this->prefix . $queue . ':processing';
    }

    /**
     * Move delayed jobs that are ready to the main queue
     */
    protected function migrateDelayedJobs(string $queue): void
    {
        $now = time();
        $delayedKey = $this->getDelayedKey($queue);

        // Get jobs ready to be processed
        $jobs = $this->redis->zRangeByScore($delayedKey, 0, $now);

        foreach ($jobs as $job) {
            // Remove from delayed
            $this->redis->zRem($delayedKey, $job);

            // Add to main queue
            $this->redis->rPush($this->getQueueKey($queue), $job);
        }
    }

    /**
     * Get connection
     */
    public function getRedis(): Redis
    {
        return $this->redis;
    }
}
