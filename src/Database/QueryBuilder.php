<?php

namespace Nexus\Database;

class QueryBuilder
{
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $columns = ['*'];
    protected ?int $limitValue = null;
    protected ?int $offsetValue = null;
    protected array $orders = [];
    protected array $joins = [];

    public function __construct(
        protected Database $database,
        protected string $table
    ) {
    }

    /**
     * Set the columns to select
     */
    public function select(array|string $columns = ['*']): self
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * Add a WHERE clause
     */
    public function where(string $column, mixed $operator, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = ['type' => 'basic', 'column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => 'AND'];
        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Add an OR WHERE clause
     */
    public function orWhere(string $column, mixed $operator, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = ['type' => 'basic', 'column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => 'OR'];
        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Add a WHERE IN clause
     */
    public function whereIn(string $column, array $values): self
    {
        $this->wheres[] = ['type' => 'in', 'column' => $column, 'values' => $values, 'boolean' => 'AND'];
        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    /**
     * Add a WHERE NULL clause
     */
    public function whereNull(string $column): self
    {
        $this->wheres[] = ['type' => 'null', 'column' => $column, 'boolean' => 'AND'];
        return $this;
    }

    /**
     * Add a WHERE NOT NULL clause
     */
    public function whereNotNull(string $column): self
    {
        $this->wheres[] = ['type' => 'not_null', 'column' => $column, 'boolean' => 'AND'];
        return $this;
    }

    /**
     * Add an ORDER BY clause
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orders[] = ['column' => $column, 'direction' => strtoupper($direction)];
        return $this;
    }

    /**
     * Set the LIMIT
     */
    public function limit(int $value): self
    {
        $this->limitValue = $value;
        return $this;
    }

    /**
     * Set the OFFSET
     */
    public function offset(int $value): self
    {
        $this->offsetValue = $value;
        return $this;
    }

    /**
     * Add a JOIN clause
     */
    public function join(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = ['type' => 'INNER', 'table' => $table, 'first' => $first, 'operator' => $operator, 'second' => $second];
        return $this;
    }

    /**
     * Add a LEFT JOIN clause
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = ['type' => 'LEFT', 'table' => $table, 'first' => $first, 'operator' => $operator, 'second' => $second];
        return $this;
    }

    /**
     * Get all results
     */
    public function get(): array
    {
        $sql = $this->toSql();
        return $this->database->select($sql, $this->bindings);
    }

    /**
     * Get the first result
     */
    public function first(): ?array
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }

    /**
     * Find a record by ID
     */
    public function find(int|string $id): ?array
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Get the count of results
     */
    public function count(): int
    {
        $originalColumns = $this->columns;
        $this->columns = ['COUNT(*) as count'];

        $result = $this->first();
        $this->columns = $originalColumns;

        return (int) ($result['count'] ?? 0);
    }

    /**
     * Insert a record
     */
    public function insert(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";

        return $this->database->insert($sql, array_values($data));
    }

    /**
     * Update records
     */
    public function update(array $data): int
    {
        $sets = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET $sets" . $this->compileWheres();

        return $this->database->update($sql, array_merge(array_values($data), $this->bindings));
    }

    /**
     * Delete records
     */
    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}" . $this->compileWheres();

        return $this->database->delete($sql, $this->bindings);
    }

    /**
     * Build the SQL query
     */
    public function toSql(): string
    {
        $columns = implode(', ', $this->columns);
        $sql = "SELECT $columns FROM {$this->table}";

        // Add joins
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        // Add where clauses
        $sql .= $this->compileWheres();

        // Add order by
        if (!empty($this->orders)) {
            $orders = implode(', ', array_map(fn($o) => "{$o['column']} {$o['direction']}", $this->orders));
            $sql .= " ORDER BY $orders";
        }

        // Add limit and offset
        if ($this->limitValue !== null) {
            $sql .= " LIMIT {$this->limitValue}";
        }

        if ($this->offsetValue !== null) {
            $sql .= " OFFSET {$this->offsetValue}";
        }

        return $sql;
    }

    /**
     * Compile WHERE clauses
     */
    protected function compileWheres(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $sql = ' WHERE ';
        $clauses = [];

        foreach ($this->wheres as $i => $where) {
            $boolean = $i === 0 ? '' : " {$where['boolean']} ";

            $clause = match ($where['type']) {
                'basic' => "{$where['column']} {$where['operator']} ?",
                'in' => "{$where['column']} IN (" . implode(', ', array_fill(0, count($where['values']), '?')) . ")",
                'null' => "{$where['column']} IS NULL",
                'not_null' => "{$where['column']} IS NOT NULL",
                default => ''
            };

            $clauses[] = $boolean . $clause;
        }

        return $sql . implode('', $clauses);
    }
}
