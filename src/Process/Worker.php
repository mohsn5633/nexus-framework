<?php

namespace Nexus\Process;

use Closure;
use Exception;

/**
 * Worker
 *
 * Background worker for executing tasks
 */
class Worker
{
    protected string $id;
    protected ?Process $process = null;
    protected bool $busy = false;
    protected mixed $currentTask = null;
    protected array $stats = [
        'tasks_completed' => 0,
        'tasks_failed' => 0,
        'total_execution_time' => 0,
    ];

    /**
     * Create a new worker
     */
    public function __construct(?string $id = null)
    {
        $this->id = $id ?? uniqid('worker_', true);
    }

    /**
     * Execute a task
     */
    public function execute(Closure $task, ...$args): mixed
    {
        if ($this->busy) {
            throw new Exception("Worker is busy");
        }

        $this->busy = true;
        $this->currentTask = $task;
        $startTime = microtime(true);

        try {
            $result = $task(...$args);
            $this->stats['tasks_completed']++;
            return $result;
        } catch (Exception $e) {
            $this->stats['tasks_failed']++;
            throw $e;
        } finally {
            $executionTime = microtime(true) - $startTime;
            $this->stats['total_execution_time'] += $executionTime;
            $this->busy = false;
            $this->currentTask = null;
        }
    }

    /**
     * Execute a task asynchronously
     */
    public function executeAsync(Closure $task, ...$args): Process
    {
        if ($this->busy) {
            throw new Exception("Worker is busy");
        }

        $this->busy = true;
        $this->currentTask = $task;

        // Serialize the task
        $serialized = base64_encode(serialize([
            'task' => $task,
            'args' => $args
        ]));

        // Create a PHP process to execute the task
        $command = sprintf(
            '%s -r "%s"',
            PHP_BINARY,
            'extract(unserialize(base64_decode(\'' . $serialized . '\')));' .
            'echo json_encode([\'result\' => call_user_func_array($task, $args)]);'
        );

        $this->process = new Process($command);
        $this->process->start();

        return $this->process;
    }

    /**
     * Wait for current task to complete
     */
    public function wait(): mixed
    {
        if (!$this->process) {
            return null;
        }

        $this->process->wait();
        $output = $this->process->getOutput();

        $this->busy = false;
        $this->currentTask = null;

        if ($this->process->isSuccessful()) {
            $this->stats['tasks_completed']++;
            $result = json_decode($output, true);
            return $result['result'] ?? null;
        }

        $this->stats['tasks_failed']++;
        throw new Exception("Task failed: " . $this->process->getErrorOutput());
    }

    /**
     * Check if worker is busy
     */
    public function isBusy(): bool
    {
        if ($this->process && $this->process->isRunning()) {
            return true;
        }

        return $this->busy;
    }

    /**
     * Get worker ID
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get worker statistics
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * Reset statistics
     */
    public function resetStats(): void
    {
        $this->stats = [
            'tasks_completed' => 0,
            'tasks_failed' => 0,
            'total_execution_time' => 0,
        ];
    }

    /**
     * Stop the worker
     */
    public function stop(): void
    {
        if ($this->process && $this->process->isRunning()) {
            $this->process->stop();
        }

        $this->busy = false;
        $this->currentTask = null;
    }
}
