<?php

namespace Nexus\Process;

use Closure;
use Exception;

/**
 * Process Pool
 *
 * Manages a pool of workers for parallel processing
 */
class ProcessPool
{
    protected array $workers = [];
    protected array $tasks = [];
    protected int $maxWorkers;
    protected int $timeout;
    protected array $results = [];
    protected array $errors = [];

    /**
     * Create a new process pool
     */
    public function __construct(int $maxWorkers = 4, int $timeout = 300)
    {
        $this->maxWorkers = $maxWorkers;
        $this->timeout = $timeout;

        // Initialize workers
        for ($i = 0; $i < $maxWorkers; $i++) {
            $this->workers[] = new Worker("worker_{$i}");
        }
    }

    /**
     * Add a task to the pool
     */
    public function add(Closure $task, ...$args): self
    {
        $this->tasks[] = [
            'task' => $task,
            'args' => $args,
            'id' => uniqid('task_', true)
        ];

        return $this;
    }

    /**
     * Execute all tasks in parallel
     */
    public function run(): array
    {
        $this->results = [];
        $this->errors = [];
        $activeTasks = [];
        $taskIndex = 0;

        while ($taskIndex < count($this->tasks) || !empty($activeTasks)) {
            // Assign tasks to available workers
            foreach ($this->workers as $worker) {
                if (!$worker->isBusy() && $taskIndex < count($this->tasks)) {
                    $taskData = $this->tasks[$taskIndex];

                    try {
                        $process = $worker->executeAsync($taskData['task'], ...$taskData['args']);
                        $activeTasks[$taskData['id']] = [
                            'worker' => $worker,
                            'process' => $process,
                            'taskData' => $taskData
                        ];
                        $taskIndex++;
                    } catch (Exception $e) {
                        $this->errors[$taskData['id']] = $e->getMessage();
                        $taskIndex++;
                    }
                }
            }

            // Check completed tasks
            foreach ($activeTasks as $taskId => $activeTask) {
                $process = $activeTask['process'];

                if (!$process->isRunning()) {
                    try {
                        $result = $activeTask['worker']->wait();
                        $this->results[$taskId] = $result;
                    } catch (Exception $e) {
                        $this->errors[$taskId] = $e->getMessage();
                    }

                    unset($activeTasks[$taskId]);
                }
            }

            usleep(10000); // 10ms
        }

        return $this->results;
    }

    /**
     * Map a callback over an array in parallel
     */
    public function map(array $items, Closure $callback): array
    {
        foreach ($items as $key => $item) {
            $this->add(function () use ($callback, $item) {
                return $callback($item);
            });
        }

        return $this->run();
    }

    /**
     * Execute tasks in parallel and return results
     */
    public static function parallel(array $tasks): array
    {
        $pool = new self(count($tasks));

        foreach ($tasks as $task) {
            if ($task instanceof Closure) {
                $pool->add($task);
            }
        }

        return $pool->run();
    }

    /**
     * Get results
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get worker statistics
     */
    public function getStats(): array
    {
        $stats = [];

        foreach ($this->workers as $worker) {
            $stats[$worker->getId()] = $worker->getStats();
        }

        return $stats;
    }

    /**
     * Stop all workers
     */
    public function stop(): void
    {
        foreach ($this->workers as $worker) {
            $worker->stop();
        }
    }

    /**
     * Get number of workers
     */
    public function getWorkerCount(): int
    {
        return count($this->workers);
    }

    /**
     * Get available workers
     */
    public function getAvailableWorkers(): array
    {
        return array_filter($this->workers, fn($worker) => !$worker->isBusy());
    }

    /**
     * Get busy workers
     */
    public function getBusyWorkers(): array
    {
        return array_filter($this->workers, fn($worker) => $worker->isBusy());
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->stop();
    }
}
