# Database Migrations

Database migrations provide version control for your database schema, allowing you to easily modify and share the database structure with your team.

## Table of Contents

- [Introduction](#introduction)
- [Creating Migrations](#creating-migrations)
- [Migration Structure](#migration-structure)
- [Running Migrations](#running-migrations)
- [Rolling Back](#rolling-back)
- [Schema Builder](#schema-builder)
- [Examples](#examples)

## Introduction

Migrations are like version control for your database. Each migration file contains instructions to create, modify, or delete database tables and columns.

### Features

- **Version Control**: Track database changes over time
- **Team Collaboration**: Share schema changes easily
- **Rollback Support**: Undo migrations if needed
- **Fluent Schema Builder**: Easy-to-use API for defining schemas
- **Batch Tracking**: Group related migrations together

## Creating Migrations

### Using CLI

```bash
# Create a new migration
php nexus make:migration create_users_table

# Create a migration for posts
php nexus make:migration create_posts_table
```

Migration files are created in `database/migrations/` with a timestamp prefix:
```
database/migrations/
├── 2025_01_19_120000_create_users_table.php
└── 2025_01_19_120100_create_posts_table.php
```

## Migration Structure

A migration file contains a class with two methods:

```php
<?php

use Nexus\Database\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        $this->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        $this->dropIfExists('users');
    }
}
```

### up() Method

The `up()` method runs when executing migrations. It should contain the schema changes you want to apply.

### down() Method

The `down()` method runs when rolling back migrations. It should reverse the changes made in `up()`.

## Running Migrations

### Run All Pending Migrations

```bash
php nexus migrate
```

Output:
```
Running migrations...
✓ 2025_01_19_120000_create_users_table.php
✓ 2025_01_19_120100_create_posts_table.php
Migrations completed successfully
```

### Check Migration Status

```bash
php nexus migrate:status
```

Output:
```
Status | Migration                              | Batch | Executed At
---------------------------------------------------------------------
✓ Ran  | 2025_01_19_120000_create_users_table  | 1     | 2025-01-19 12:30:00
✗ Pending | 2025_01_19_120100_create_posts_table | -  | -
```

## Rolling Back

### Rollback Last Batch

```bash
php nexus migrate:rollback
```

### Rollback Multiple Batches

```bash
# Rollback last 3 batches
php nexus migrate:rollback --steps=3
```

### Rollback All Migrations

```bash
php nexus migrate:reset
```

## Schema Builder

The schema builder provides a fluent interface for defining table structures.

### Creating Tables

```php
$this->create('users', function ($table) {
    // Define columns
});
```

### Modifying Tables

```php
$this->table('users', function ($table) {
    // Add new columns
});
```

### Dropping Tables

```php
$this->dropIfExists('users');
```

### Column Types

#### Integers

```php
$table->id();                           // Auto-incrementing BIGINT primary key
$table->integer('votes');               // INT
$table->bigInteger('user_id');          // BIGINT
$table->bigInteger('amount')->unsigned(); // BIGINT UNSIGNED
```

#### Strings

```php
$table->string('name');                 // VARCHAR(255)
$table->string('email', 100);           // VARCHAR(100)
$table->text('description');            // TEXT
```

#### Booleans

```php
$table->boolean('is_active');           // TINYINT(1)
$table->boolean('confirmed')->default(false);
```

#### Decimals

```php
$table->decimal('amount');              // DECIMAL(8, 2)
$table->decimal('price', 10, 2);        // DECIMAL(10, 2)
```

#### Dates and Times

```php
$table->date('birth_date');             // DATE
$table->dateTime('created_at');         // DATETIME
$table->timestamp('updated_at');        // TIMESTAMP
$table->timestamps();                   // created_at & updated_at
```

### Column Modifiers

```php
// Nullable
$table->string('middle_name')->nullable();

// Default value
$table->boolean('is_active')->default(true);
$table->integer('status')->default(0);

// Unsigned
$table->integer('votes')->unsigned();

// Unique
$table->string('email')->unique();

// Combined modifiers
$table->string('nickname')->nullable()->unique();
$table->integer('points')->default(0)->unsigned();
```

### Indexes

```php
// Single column index
$table->string('email')->unique();
$table->index('user_id');

// Multiple column index
$table->index(['user_id', 'post_id']);

// Unique constraint
$table->unique('email');
$table->unique(['user_id', 'post_id'], 'unique_user_post');
```

## Examples

### Create Users Table

```php
<?php

use Nexus\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->dropIfExists('users');
    }
}
```

### Create Posts Table

```php
<?php

use Nexus\Database\Migration;

class CreatePostsTable extends Migration
{
    public function up(): void
    {
        $this->create('posts', function ($table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('status')->default('draft');
            $table->integer('views')->default(0)->unsigned();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('posts');
    }
}
```

### Create Comments Table

```php
<?php

use Nexus\Database\Migration;

class CreateCommentsTable extends Migration
{
    public function up(): void
    {
        $this->create('comments', function ($table) {
            $table->id();
            $table->foreignId('post_id');
            $table->foreignId('user_id');
            $table->foreignId('parent_id')->nullable();
            $table->text('content');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('post_id');
            $table->index('user_id');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('comments');
    }
}
```

### Add Column to Existing Table

```php
<?php

use Nexus\Database\Migration;

class AddPhoneToUsersTable extends Migration
{
    public function up(): void
    {
        $this->table('users', function ($table) {
            $table->string('phone', 20)->nullable();
        });
    }

    public function down(): void
    {
        // Note: Removing columns requires manual SQL
        $this->db->execute('ALTER TABLE users DROP COLUMN phone');
    }
}
```

### Create Products Table

```php
<?php

use Nexus\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up(): void
    {
        $this->create('products', function ($table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('stock')->default(0)->unsigned();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('sku');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('products');
    }
}
```

### Create Orders Table

```php
<?php

use Nexus\Database\Migration;

class CreateOrdersTable extends Migration
{
    public function up(): void
    {
        $this->create('orders', function ($table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('order_number', 50)->unique();
            $table->string('status')->default('pending');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('order_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        $this->dropIfExists('orders');
    }
}
```

## Best Practices

1. **Descriptive Names**: Use clear migration names (e.g., `create_users_table`)
2. **Always Provide Rollback**: Implement the `down()` method for every migration
3. **Test Rollbacks**: Ensure migrations can be rolled back successfully
4. **Small Migrations**: Create focused migrations for specific changes
5. **Never Edit Ran Migrations**: Create new migrations for changes
6. **Use Timestamps**: Leverage `timestamps()` for created_at/updated_at
7. **Add Indexes**: Index foreign keys and frequently queried columns
8. **Test on Development**: Run migrations on development first

## Common Patterns

### Soft Deletes

```php
$table->timestamp('deleted_at')->nullable();
```

### Polymorphic Relations

```php
$table->string('commentable_type');
$table->bigInteger('commentable_id')->unsigned();
$table->index(['commentable_type', 'commentable_id']);
```

### Enum-like Columns

```php
$table->string('status')->default('pending');
// Values: pending, approved, rejected
```

### JSON Columns

```php
$table->text('metadata'); // Store JSON as TEXT
```

## Troubleshooting

### Migration Failed

If a migration fails:

1. Check the error message for SQL errors
2. Verify column types and constraints
3. Ensure table names are correct
4. Check for duplicate migrations

### Can't Rollback

If rollback fails:

1. Check the `down()` method implementation
2. Verify the table still exists
3. Check for foreign key constraints

## Next Steps

- Learn about [Database Seeders](seeders.md)
- Understand [Models](models.md)
- Explore [Query Builder](query-builder.md)
