<?php

namespace Nexus\Database;

/**
 * Base Migration Class
 *
 * All migrations should extend this class and implement up() and down() methods
 */
abstract class Migration
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Run the migrations
     */
    abstract public function up(): void;

    /**
     * Reverse the migrations
     */
    abstract public function down(): void;

    /**
     * Create a new table
     *
     * @param string $table Table name
     * @param callable $callback Column definitions callback
     */
    protected function create(string $table, callable $callback): void
    {
        $schema = new Schema($table, $this->db);
        $callback($schema);
        $schema->create();
    }

    /**
     * Drop a table
     *
     * @param string $table Table name
     */
    protected function dropIfExists(string $table): void
    {
        $this->db->execute("DROP TABLE IF EXISTS `{$table}`");
    }

    /**
     * Alter an existing table
     *
     * @param string $table Table name
     * @param callable $callback Modification callback
     */
    protected function table(string $table, callable $callback): void
    {
        $schema = new Schema($table, $this->db, false);
        $callback($schema);
        $schema->alter();
    }
}

/**
 * Schema Builder Class
 *
 * Provides a fluent interface for building table schemas
 */
class Schema
{
    protected string $table;
    protected Database $db;
    protected array $columns = [];
    protected array $indexes = [];
    protected bool $isCreating;

    public function __construct(string $table, Database $db, bool $isCreating = true)
    {
        $this->table = $table;
        $this->db = $db;
        $this->isCreating = $isCreating;
    }

    /**
     * Add an auto-incrementing ID column
     */
    public function id(string $name = 'id'): self
    {
        $this->columns[] = "`{$name}` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    /**
     * Add a string column
     */
    public function string(string $name, int $length = 255): ColumnDefinition
    {
        return new ColumnDefinition($this, $name, "VARCHAR({$length})");
    }

    /**
     * Add a text column
     */
    public function text(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, $name, 'TEXT');
    }

    /**
     * Add an integer column
     */
    public function integer(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, $name, 'INT');
    }

    /**
     * Add a big integer column
     */
    public function bigInteger(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, $name, 'BIGINT');
    }

    /**
     * Add a boolean column
     */
    public function boolean(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, $name, 'TINYINT(1)');
    }

    /**
     * Add a decimal column
     */
    public function decimal(string $name, int $total = 8, int $places = 2): ColumnDefinition
    {
        return new ColumnDefinition($this, $name, "DECIMAL({$total}, {$places})");
    }

    /**
     * Add a date column
     */
    public function date(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, $name, 'DATE');
    }

    /**
     * Add a datetime column
     */
    public function dateTime(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, $name, 'DATETIME');
    }

    /**
     * Add a timestamp column
     */
    public function timestamp(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, $name, 'TIMESTAMP');
    }

    /**
     * Add timestamps (created_at and updated_at)
     */
    public function timestamps(): self
    {
        $this->timestamp('created_at')->nullable()->default('CURRENT_TIMESTAMP');
        $this->timestamp('updated_at')->nullable()->default('CURRENT_TIMESTAMP')->onUpdate('CURRENT_TIMESTAMP');
        return $this;
    }

    /**
     * Add a foreign key column
     */
    public function foreignId(string $name): ColumnDefinition
    {
        return new ColumnDefinition($this, $name, 'BIGINT UNSIGNED');
    }

    /**
     * Add a column definition
     */
    public function addColumn(string $definition): void
    {
        $this->columns[] = $definition;
    }

    /**
     * Add an index
     */
    public function index(string|array $columns, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $indexName = $name ?? $this->table . '_' . implode('_', $columns) . '_index';
        $columnList = implode('`, `', $columns);

        $this->indexes[] = "INDEX `{$indexName}` (`{$columnList}`)";
        return $this;
    }

    /**
     * Add a unique index
     */
    public function unique(string|array $columns, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $indexName = $name ?? $this->table . '_' . implode('_', $columns) . '_unique';
        $columnList = implode('`, `', $columns);

        $this->indexes[] = "UNIQUE KEY `{$indexName}` (`{$columnList}`)";
        return $this;
    }

    /**
     * Create the table
     */
    public function create(): void
    {
        $columns = implode(",\n    ", array_merge($this->columns, $this->indexes));

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (\n    {$columns}\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->execute($sql);
    }

    /**
     * Alter the table
     */
    public function alter(): void
    {
        foreach ($this->columns as $column) {
            $sql = "ALTER TABLE `{$this->table}` ADD {$column}";
            $this->db->execute($sql);
        }

        foreach ($this->indexes as $index) {
            $sql = "ALTER TABLE `{$this->table}` ADD {$index}";
            $this->db->execute($sql);
        }
    }
}

/**
 * Column Definition Builder
 */
class ColumnDefinition
{
    protected Schema $schema;
    protected string $name;
    protected string $type;
    protected array $modifiers = [];

    public function __construct(Schema $schema, string $name, string $type)
    {
        $this->schema = $schema;
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Make column nullable
     */
    public function nullable(): self
    {
        $this->modifiers[] = 'NULL';
        return $this;
    }

    /**
     * Set default value
     */
    public function default(mixed $value): self
    {
        if ($value === 'CURRENT_TIMESTAMP' || $value === 'NULL') {
            $this->modifiers[] = "DEFAULT {$value}";
        } else {
            $escapedValue = is_string($value) ? "'{$value}'" : $value;
            $this->modifiers[] = "DEFAULT {$escapedValue}";
        }
        return $this;
    }

    /**
     * Make column unsigned
     */
    public function unsigned(): self
    {
        $this->type .= ' UNSIGNED';
        return $this;
    }

    /**
     * Add ON UPDATE clause
     */
    public function onUpdate(string $value): self
    {
        $this->modifiers[] = "ON UPDATE {$value}";
        return $this;
    }

    /**
     * Add unique constraint
     */
    public function unique(): self
    {
        $this->modifiers[] = 'UNIQUE';
        return $this;
    }

    /**
     * Build the column definition and add to schema
     */
    public function __destruct()
    {
        $notNull = !in_array('NULL', $this->modifiers) ? 'NOT NULL' : '';
        $modifiers = implode(' ', $this->modifiers);

        $definition = "`{$this->name}` {$this->type} {$notNull} {$modifiers}";
        $definition = trim(preg_replace('/\s+/', ' ', $definition));

        $this->schema->addColumn($definition);
    }
}
