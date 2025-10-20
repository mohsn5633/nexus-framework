<?php

namespace Nexus\Queue;

/**
 * Dispatchable Trait
 *
 * Allows jobs to be dispatched to the queue
 */
trait Dispatchable
{
    /**
     * Dispatch the job
     */
    public static function dispatch(...$arguments): mixed
    {
        return app(QueueManager::class)->push(static::class, $arguments);
    }

    /**
     * Dispatch the job after a delay
     */
    public static function dispatchAfter(int $delay, ...$arguments): mixed
    {
        return app(QueueManager::class)->later($delay, static::class, $arguments);
    }

    /**
     * Dispatch the job to a specific queue
     */
    public static function dispatchOn(string $queue, ...$arguments): mixed
    {
        return app(QueueManager::class)->push(static::class, $arguments, $queue);
    }
}
