<?php

namespace Nexus\Queue;

use Nexus\Queue\Drivers\SyncQueue;
use Nexus\Queue\Drivers\DatabaseQueue;
use Nexus\Queue\Drivers\RedisQueue;
use Nexus\Database\Database;

/**
 * Queue Manager
 *
 * Manages queue connections and dispatches jobs
 */
class QueueManager
{
    protected array $config;
    protected array $drivers = [];
    protected ?Database $db = null;

    public function __construct(array $config, ?Database $db = null)
    {
        $this->config = $config;
        $this->db = $db;
    }

    /**
     * Get a queue connection
     */
    public function connection(?string $name = null): QueueInterface
    {
        $name = $name ?? $this->config['default'];

        if (isset($this->drivers[$name])) {
            return $this->drivers[$name];
        }

        return $this->drivers[$name] = $this->resolve($name);
    }

    /**
     * Resolve a queue driver
     */
    protected function resolve(string $name): QueueInterface
    {
        $config = $this->config['connections'][$name] ?? [];

        return match($config['driver'] ?? 'sync') {
            'sync' => new SyncQueue(),
            'database' => new DatabaseQueue($this->db, $config),
            'redis' => new RedisQueue($config),
            default => throw new \InvalidArgumentException("Queue driver [{$config['driver']}] not supported.")
        };
    }

    /**
     * Push a job onto the default queue
     */
    public function push(string $job, array $data = [], ?string $queue = null): mixed
    {
        return $this->connection()->push($job, $data, $queue);
    }

    /**
     * Push a job with delay
     */
    public function later(int $delay, string $job, array $data = [], ?string $queue = null): mixed
    {
        return $this->connection()->later($delay, $job, $data, $queue);
    }

    /**
     * Get queue size
     */
    public function size(?string $queue = null): int
    {
        return $this->connection()->size($queue);
    }

    /**
     * Get the default connection name
     */
    public function getDefaultDriver(): string
    {
        return $this->config['default'];
    }

    /**
     * Set the default connection name
     */
    public function setDefaultDriver(string $name): void
    {
        $this->config['default'] = $name;
    }
}
