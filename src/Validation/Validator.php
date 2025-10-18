<?php

namespace Nexus\Validation;

use Nexus\Database\DB;

class Validator
{
    protected array $data;
    protected array $rules;
    protected array $messages;
    protected array $errors = [];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    /**
     * Run validation
     */
    public function validate(): array
    {
        foreach ($this->rules as $field => $rules) {
            $rulesArray = is_string($rules) ? explode('|', $rules) : $rules;

            foreach ($rulesArray as $rule) {
                $this->validateRule($field, $rule);
            }
        }

        if ($this->fails()) {
            throw new ValidationException($this->errors);
        }

        return $this->validated();
    }

    /**
     * Validate a single rule
     */
    protected function validateRule(string $field, string $rule): void
    {
        // Parse rule and parameters
        [$ruleName, $parameters] = $this->parseRule($rule);

        // Get the value
        $value = $this->getValue($field);

        // Skip validation if field is not required and is empty
        if (!$this->isRequired($field) && $this->isEmpty($value) && $ruleName !== 'required') {
            return;
        }

        // Call validation method
        $method = 'validate' . ucfirst($ruleName);

        if (method_exists($this, $method)) {
            if (!$this->$method($field, $value, $parameters)) {
                $this->addError($field, $ruleName, $parameters);
            }
        }
    }

    /**
     * Parse rule string
     */
    protected function parseRule(string $rule): array
    {
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $parameters = isset($parts[1]) ? explode(',', $parts[1]) : [];

        return [$ruleName, $parameters];
    }

    /**
     * Get value from data
     */
    protected function getValue(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    /**
     * Check if field is required
     */
    protected function isRequired(string $field): bool
    {
        $rules = $this->rules[$field] ?? [];
        $rulesArray = is_string($rules) ? explode('|', $rules) : $rules;

        return in_array('required', $rulesArray);
    }

    /**
     * Check if value is empty
     */
    protected function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    /**
     * Add validation error
     */
    protected function addError(string $field, string $rule, array $parameters = []): void
    {
        $message = $this->getMessage($field, $rule, $parameters);
        $this->errors[$field][] = $message;
    }

    /**
     * Get error message
     */
    protected function getMessage(string $field, string $rule, array $parameters = []): string
    {
        $key = "{$field}.{$rule}";

        if (isset($this->messages[$key])) {
            return $this->messages[$key];
        }

        return $this->getDefaultMessage($field, $rule, $parameters);
    }

    /**
     * Get default error message
     */
    protected function getDefaultMessage(string $field, string $rule, array $parameters = []): string
    {
        $field = str_replace('_', ' ', $field);

        return match($rule) {
            'required' => "The {$field} field is required.",
            'email' => "The {$field} must be a valid email address.",
            'min' => "The {$field} must be at least {$parameters[0]} characters.",
            'max' => "The {$field} must not exceed {$parameters[0]} characters.",
            'numeric' => "The {$field} must be a number.",
            'string' => "The {$field} must be a string.",
            'url' => "The {$field} must be a valid URL.",
            'in' => "The selected {$field} is invalid.",
            'confirmed' => "The {$field} confirmation does not match.",
            'unique' => "The {$field} has already been taken.",
            'exists' => "The selected {$field} is invalid.",
            'regex' => "The {$field} format is invalid.",
            'alpha' => "The {$field} must only contain letters.",
            'alpha_num' => "The {$field} must only contain letters and numbers.",
            'boolean' => "The {$field} field must be true or false.",
            'date' => "The {$field} is not a valid date.",
            'integer' => "The {$field} must be an integer.",
            'array' => "The {$field} must be an array.",
            default => "The {$field} is invalid.",
        };
    }

    /**
     * Validation Rules
     */

    protected function validateRequired(string $field, mixed $value, array $parameters): bool
    {
        return !$this->isEmpty($value);
    }

    protected function validateEmail(string $field, mixed $value, array $parameters): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateMin(string $field, mixed $value, array $parameters): bool
    {
        $min = (int) $parameters[0];

        if (is_numeric($value)) {
            return $value >= $min;
        }

        return mb_strlen((string) $value) >= $min;
    }

    protected function validateMax(string $field, mixed $value, array $parameters): bool
    {
        $max = (int) $parameters[0];

        if (is_numeric($value)) {
            return $value <= $max;
        }

        return mb_strlen((string) $value) <= $max;
    }

    protected function validateNumeric(string $field, mixed $value, array $parameters): bool
    {
        return is_numeric($value);
    }

    protected function validateString(string $field, mixed $value, array $parameters): bool
    {
        return is_string($value);
    }

    protected function validateUrl(string $field, mixed $value, array $parameters): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    protected function validateIn(string $field, mixed $value, array $parameters): bool
    {
        return in_array($value, $parameters);
    }

    protected function validateConfirmed(string $field, mixed $value, array $parameters): bool
    {
        $confirmField = $field . '_confirmation';
        return isset($this->data[$confirmField]) && $value === $this->data[$confirmField];
    }

    protected function validateUnique(string $field, mixed $value, array $parameters): bool
    {
        if (count($parameters) < 1) {
            return false;
        }

        $table = $parameters[0];
        $column = $parameters[1] ?? $field;
        $ignore = $parameters[2] ?? null;

        $query = DB::table($table)->where($column, $value);

        if ($ignore !== null) {
            $ignoreColumn = $parameters[3] ?? 'id';
            $query->where($ignoreColumn, '!=', $ignore);
        }

        return $query->count() === 0;
    }

    protected function validateExists(string $field, mixed $value, array $parameters): bool
    {
        if (count($parameters) < 1) {
            return false;
        }

        $table = $parameters[0];
        $column = $parameters[1] ?? $field;

        return DB::table($table)->where($column, $value)->count() > 0;
    }

    protected function validateRegex(string $field, mixed $value, array $parameters): bool
    {
        if (count($parameters) < 1) {
            return false;
        }

        return preg_match($parameters[0], (string) $value) > 0;
    }

    protected function validateAlpha(string $field, mixed $value, array $parameters): bool
    {
        return preg_match('/^[a-zA-Z]+$/', (string) $value) > 0;
    }

    protected function validateAlphaNum(string $field, mixed $value, array $parameters): bool
    {
        return preg_match('/^[a-zA-Z0-9]+$/', (string) $value) > 0;
    }

    protected function validateBoolean(string $field, mixed $value, array $parameters): bool
    {
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }

    protected function validateDate(string $field, mixed $value, array $parameters): bool
    {
        return strtotime((string) $value) !== false;
    }

    protected function validateInteger(string $field, mixed $value, array $parameters): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function validateArray(string $field, mixed $value, array $parameters): bool
    {
        return is_array($value);
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get validated data
     */
    public function validated(): array
    {
        $validated = [];

        foreach (array_keys($this->rules) as $field) {
            if (isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }

        return $validated;
    }
}
