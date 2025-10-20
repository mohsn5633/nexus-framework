<?php

namespace Nexus\Queue\Drivers;

use Nexus\Queue\QueueInterface;
use Nexus\Queue\Job;
use Nexus\Database\Database;

/**
 * Database Queue Driver
 *
 * Stores jobs in a database table
 */
class DatabaseQueue implements QueueInterface
{
    protected Database $db;
    protected string $table;
    protected string $defaultQueue;

    public function __construct(Database $db, array $config = [])
    {
        $this->db = $db;
        $this->table = $config['table'] ?? 'jobs';
        $this->defaultQueue = $config['queue'] ?? 'default';
    }

    /**
     * Push a job onto the queue
     */
    public function push(string $job, array $data = [], ?string $queue = null): mixed
    {
        return $this->pushToDatabase($job, $data, $queue, 0);
    }

    /**
     * Push a job with delay
     */
    public function later(int $delay, string $job, array $data = [], ?string $queue = null): mixed
    {
        return $this->pushToDatabase($job, $data, $queue, $delay);
    }

    /**
     * Push job to database
     */
    protected function pushToDatabase(string $job, array $data, ?string $queue, int $delay): mixed
    {
        $queue = $queue ?? $this->defaultQueue;
        $availableAt = time() + $delay;

        $payload = $this->createPayload($job, $data);

        return $this->db->table($this->table)->insert([
            'queue' => $queue,
            'payload' => $payload,
            'attempts' => 0,
            'available_at' => $availableAt,
            'created_at' => time(),
        ]);
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
     * Pop the next job off the queue
     */
    public function pop(?string $queue = null): ?Job
    {
        $queue = $queue ?? $this->defaultQueue;

        // Get next available job
        $job = $this->db->query(
            "SELECT * FROM {$this->table}
             WHERE queue = ?
             AND available_at <= ?
             AND reserved_at IS NULL
             ORDER BY id ASC
             LIMIT 1",
            [$queue, time()]
        );

        if (empty($job)) {
            return null;
        }

        $job = $job[0];

        // Reserve the job
        $this->db->execute(
            "UPDATE {$this->table}
             SET reserved_at = ?, attempts = attempts + 1
             WHERE id = ?",
            [time(), $job['id']]
        );

        $payload = json_decode($job['payload'], true);

        return new Job(
            $job['id'],
            $job['queue'],
            $payload['job'],
            $payload['data'],
            $job['attempts'] + 1
        );
    }

    /**
     * Get queue size
     */
    public function size(?string $queue = null): int
    {
        $queue = $queue ?? $this->defaultQueue;

        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE queue = ? AND reserved_at IS NULL",
            [$queue]
        );

        return (int) ($result[0]['count'] ?? 0);
    }

    /**
     * Delete a job
     */
    public function delete(mixed $id): bool
    {
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$id]
        ) !== false;
    }

    /**
     * Release a reserved job back to the queue
     */
    public function release(mixed $id, int $delay = 0): bool
    {
        $availableAt = time() + $delay;

        return $this->db->execute(
            "UPDATE {$this->table}
             SET reserved_at = NULL, available_at = ?
             WHERE id = ?",
            [$availableAt, $id]
        ) !== false;
    }

    /**
     * Move job to failed jobs table
     */
    public function failed(mixed $id, \Exception $exception): bool
    {
        $job = $this->db->query(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );

        if (empty($job)) {
            return false;
        }

        $job = $job[0];

        // Insert into failed_jobs table
        $this->db->table('failed_jobs')->insert([
            'queue' => $job['queue'],
            'payload' => $job['payload'],
            'exception' => $exception->getMessage(),
            'failed_at' => time(),
        ]);

        // Delete from jobs table
        return $this->delete($id);
    }
}
