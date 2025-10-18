<?php

namespace Nexus\Database;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    protected ?PDO $pdo = null;

    public function __construct(
        protected array $config
    ) {
    }

    /**
     * Get the PDO connection
     */
    public function connection(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        return $this->pdo;
    }

    /**
     * Establish database connection
     */
    protected function connect(): void
    {
        $driver = $this->config['driver'] ?? 'mysql';
        $host = $this->config['host'] ?? 'localhost';
        $port = $this->config['port'] ?? 3306;
        $database = $this->config['database'] ?? '';
        $username = $this->config['username'] ?? 'root';
        $password = $this->config['password'] ?? '';
        $charset = $this->config['charset'] ?? 'utf8mb4';

        try {
            $dsn = match ($driver) {
                'mysql' => "mysql:host=$host;port=$port;dbname=$database;charset=$charset",
                'pgsql' => "pgsql:host=$host;port=$port;dbname=$database",
                'sqlite' => "sqlite:$database",
                default => throw new \Exception("Unsupported database driver: $driver")
            };

            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Execute a query and return the statement
     */
    public function query(string $sql, array $bindings = []): PDOStatement
    {
        $statement = $this->connection()->prepare($sql);
        $statement->execute($bindings);
        return $statement;
    }

    /**
     * Execute a select query
     */
    public function select(string $sql, array $bindings = []): array
    {
        return $this->query($sql, $bindings)->fetchAll();
    }

    /**
     * Execute an insert query
     */
    public function insert(string $sql, array $bindings = []): int
    {
        $this->query($sql, $bindings);
        return (int) $this->connection()->lastInsertId();
    }

    /**
     * Execute an update query
     */
    public function update(string $sql, array $bindings = []): int
    {
        return $this->query($sql, $bindings)->rowCount();
    }

    /**
     * Execute a delete query
     */
    public function delete(string $sql, array $bindings = []): int
    {
        return $this->query($sql, $bindings)->rowCount();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool
    {
        return $this->connection()->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit(): bool
    {
        return $this->connection()->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback(): bool
    {
        return $this->connection()->rollBack();
    }

    /**
     * Create a new query builder
     */
    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }
}
