<?php

namespace Nexus\Database;

abstract class Model
{
    protected static ?string $table = null;
    protected static string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Get the table name
     */
    public static function getTable(): string
    {
        if (static::$table) {
            return static::$table;
        }

        // Convert class name to snake_case plural
        $class = basename(str_replace('\\', '/', static::class));
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
    }

    /**
     * Get a new query builder instance
     */
    public static function query(): QueryBuilder
    {
        return app('db')->table(static::getTable());
    }

    /**
     * Find a model by ID
     */
    public static function find(int|string $id): ?static
    {
        $result = static::query()->find($id);

        if ($result) {
            $model = new static($result);
            $model->exists = true;
            $model->original = $result;
            return $model;
        }

        return null;
    }

    /**
     * Get all models
     */
    public static function all(): array
    {
        $results = static::query()->get();

        return array_map(function ($result) {
            $model = new static($result);
            $model->exists = true;
            $model->original = $result;
            return $model;
        }, $results);
    }

    /**
     * Create a new model
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Fill the model with attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * Save the model
     */
    public function save(): bool
    {
        if ($this->exists) {
            // Update existing record
            $id = $this->attributes[static::$primaryKey];
            $changes = array_diff_assoc($this->attributes, $this->original);

            if (empty($changes)) {
                return true;
            }

            static::query()->where(static::$primaryKey, $id)->update($changes);
            $this->original = $this->attributes;
        } else {
            // Insert new record
            $id = static::query()->insert($this->attributes);
            $this->attributes[static::$primaryKey] = $id;
            $this->original = $this->attributes;
            $this->exists = true;
        }

        return true;
    }

    /**
     * Delete the model
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $id = $this->attributes[static::$primaryKey];
        static::query()->where(static::$primaryKey, $id)->delete();

        $this->exists = false;

        return true;
    }

    /**
     * Get an attribute
     */
    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set an attribute
     */
    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Check if an attribute exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convert to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->attributes);
    }
}
