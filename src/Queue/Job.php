<?php

namespace Nexus\Queue;

/**
 * Job Class
 *
 * Represents a queued job
 */
class Job
{
    protected mixed $id;
    protected string $queue;
    protected string $jobClass;
    protected array $data;
    protected int $attempts = 0;
    protected ?int $maxTries = null;
    protected ?int $timeout = null;

    public function __construct(
        mixed $id,
        string $queue,
        string $jobClass,
        array $data = [],
        int $attempts = 0
    ) {
        $this->id = $id;
        $this->queue = $queue;
        $this->jobClass = $jobClass;
        $this->data = $data;
        $this->attempts = $attempts;
    }

    /**
     * Get the job ID
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * Get the queue name
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * Get the job class name
     */
    public function getJobClass(): string
    {
        return $this->jobClass;
    }

    /**
     * Get the job data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get the number of attempts
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * Increment the attempt count
     */
    public function incrementAttempts(): void
    {
        $this->attempts++;
    }

    /**
     * Set maximum tries
     */
    public function setMaxTries(?int $maxTries): void
    {
        $this->maxTries = $maxTries;
    }

    /**
     * Get maximum tries
     */
    public function getMaxTries(): ?int
    {
        return $this->maxTries;
    }

    /**
     * Check if job has exceeded max tries
     */
    public function hasExceededMaxTries(): bool
    {
        if ($this->maxTries === null) {
            return false;
        }

        return $this->attempts >= $this->maxTries;
    }

    /**
     * Fire the job
     */
    public function fire(): void
    {
        $jobInstance = new $this->jobClass();

        if (method_exists($jobInstance, 'handle')) {
            $jobInstance->handle(...array_values($this->data));
        }
    }

    /**
     * Mark job as failed
     */
    public function fail(\Exception $e): void
    {
        $jobInstance = new $this->jobClass();

        if (method_exists($jobInstance, 'failed')) {
            $jobInstance->failed($e);
        }
    }

    /**
     * Release the job back onto the queue
     */
    public function release(int $delay = 0): void
    {
        // To be implemented by queue driver
    }
}
