<?php

namespace Nexus\Schedule;

/**
 * Task Scheduler
 *
 * Manages scheduled tasks
 */
class Scheduler
{
    protected array $events = [];

    /**
     * Schedule a callback to run
     */
    public function call(callable $callback): Event
    {
        $event = new Event($callback);
        $this->events[] = $event;

        return $event;
    }

    /**
     * Schedule a job to run
     */
    public function job(string $jobClass, array $data = []): Event
    {
        return $this->call(function() use ($jobClass, $data) {
            $job = new $jobClass();
            if (method_exists($job, 'handle')) {
                $job->handle(...array_values($data));
            }
        });
    }

    /**
     * Schedule a command to run
     */
    public function command(string $command): Event
    {
        return $this->call(function() use ($command) {
            exec("php " . base_path('nexus') . " {$command}");
        });
    }

    /**
     * Schedule an exec command to run
     */
    public function exec(string $command): Event
    {
        return $this->call(function() use ($command) {
            exec($command);
        });
    }

    /**
     * Get all defined events
     */
    public function events(): array
    {
        return $this->events;
    }

    /**
     * Get all events that are due
     */
    public function dueEvents(): array
    {
        return array_filter($this->events, fn(Event $event) => $event->isDue());
    }

    /**
     * Run all due events
     */
    public function run(): void
    {
        foreach ($this->dueEvents() as $event) {
            $event->run();
        }
    }
}
