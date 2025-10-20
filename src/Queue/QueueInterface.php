<?php

namespace Nexus\Queue;

/**
 * Queue Interface
 *
 * Defines the contract for queue drivers
 */
interface QueueInterface
{
    /**
     * Push a new job onto the queue
     *
     * @param string $job Job class name
     * @param array $data Job data
     * @param string|null $queue Queue name
     * @return mixed Job ID or identifier
     */
    public function push(string $job, array $data = [], ?string $queue = null): mixed;

    /**
     * Push a new job onto the queue after a delay
     *
     * @param int $delay Delay in seconds
     * @param string $job Job class name
     * @param array $data Job data
     * @param string|null $queue Queue name
     * @return mixed Job ID or identifier
     */
    public function later(int $delay, string $job, array $data = [], ?string $queue = null): mixed;

    /**
     * Pop the next job off of the queue
     *
     * @param string|null $queue Queue name
     * @return Job|null
     */
    public function pop(?string $queue = null): ?Job;

    /**
     * Get the size of the queue
     *
     * @param string|null $queue Queue name
     * @return int
     */
    public function size(?string $queue = null): int;

    /**
     * Delete a job from the queue
     *
     * @param mixed $id Job ID
     * @return bool
     */
    public function delete(mixed $id): bool;
}
