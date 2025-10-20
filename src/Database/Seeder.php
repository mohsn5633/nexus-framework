<?php

namespace Nexus\Database;

/**
 * Base Seeder Class
 *
 * All seeders should extend this class and implement the run() method
 */
abstract class Seeder
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Run the seeder
     */
    abstract public function run(): void;

    /**
     * Call another seeder
     *
     * @param string|array $seederClass
     */
    protected function call(string|array $seederClass): void
    {
        $seeders = is_array($seederClass) ? $seederClass : [$seederClass];

        foreach ($seeders as $seeder) {
            $instance = new $seeder($this->db);
            $instance->run();
        }
    }

    /**
     * Insert data into table
     *
     * @param string $table
     * @param array $data
     * @return bool
     */
    protected function insert(string $table, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        // Check if it's a single row or multiple rows
        $isMultiple = isset($data[0]) && is_array($data[0]);

        if ($isMultiple) {
            return $this->insertMultiple($table, $data);
        }

        return $this->insertSingle($table, $data);
    }

    /**
     * Insert a single row
     *
     * @param string $table
     * @param array $data
     * @return bool
     */
    protected function insertSingle(string $table, array $data): bool
    {
        $columns = array_keys($data);
        $values = array_values($data);

        $columnList = '`' . implode('`, `', $columns) . '`';
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $sql = "INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})";

        return $this->db->execute($sql, $values) !== false;
    }

    /**
     * Insert multiple rows
     *
     * @param string $table
     * @param array $data
     * @return bool
     */
    protected function insertMultiple(string $table, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $columns = array_keys($data[0]);
        $columnList = '`' . implode('`, `', $columns) . '`';

        $values = [];
        $placeholders = [];

        foreach ($data as $row) {
            $rowValues = [];
            foreach ($columns as $column) {
                $rowValues[] = $row[$column] ?? null;
            }
            $values = array_merge($values, $rowValues);
            $placeholders[] = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        }

        $placeholderList = implode(', ', $placeholders);
        $sql = "INSERT INTO `{$table}` ({$columnList}) VALUES {$placeholderList}";

        return $this->db->execute($sql, $values) !== false;
    }

    /**
     * Truncate a table
     *
     * @param string $table
     */
    protected function truncate(string $table): void
    {
        $this->db->execute("TRUNCATE TABLE `{$table}`");
    }

    /**
     * Delete all records from a table
     *
     * @param string $table
     */
    protected function delete(string $table): void
    {
        $this->db->execute("DELETE FROM `{$table}`");
    }

    /**
     * Get the database instance
     *
     * @return Database
     */
    protected function db(): Database
    {
        return $this->db;
    }

    /**
     * Get query builder for table
     *
     * @param string $table
     * @return QueryBuilder
     */
    protected function table(string $table): QueryBuilder
    {
        return $this->db->table($table);
    }
}
