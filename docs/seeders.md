# Database Seeders

Database seeders allow you to populate your database with test or initial data. They're perfect for development, testing, and setting up initial application state.

## Table of Contents

- [Introduction](#introduction)
- [Creating Seeders](#creating-seeders)
- [Running Seeders](#running-seeders)
- [Seeder Methods](#seeder-methods)
- [Examples](#examples)
- [Best Practices](#best-practices)

## Introduction

Seeders provide a simple way to populate your database with sample data for development and testing.

### Features

- **Simple API**: Easy methods for inserting data
- **Callable Seeders**: Call other seeders from within a seeder
- **Query Builder Access**: Use full query builder capabilities
- **Batch Insertion**: Insert multiple records efficiently

## Creating Seeders

### Using CLI

```bash
# Create a seeder
php nexus make:seeder UserSeeder

# Create more seeders
php nexus make:seeder PostSeeder
php nexus make:seeder CategorySeeder
```

Seeder files are created in `database/seeders/`:

```
database/seeders/
├── DatabaseSeeder.php
├── UserSeeder.php
├── PostSeeder.php
└── CategorySeeder.php
```

### Seeder Structure

```php
<?php

use Nexus\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the seeder
     */
    public function run(): void
    {
        $this->insert('users', [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
        ]);
    }
}
```

## Running Seeders

### Run Default Seeder

```bash
php nexus db:seed
```

This runs `DatabaseSeeder` by default.

### Run Specific Seeder

```bash
php nexus db:seed --class=UserSeeder
php nexus db:seed --class=PostSeeder
```

### DatabaseSeeder

Create a main seeder that calls others:

```php
<?php

use Nexus\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
```

## Seeder Methods

### insert()

Insert data into a table:

```php
// Insert single row
$this->insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

// Insert multiple rows
$this->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### call()

Call other seeders:

```php
// Call single seeder
$this->call(UserSeeder::class);

// Call multiple seeders
$this->call([
    UserSeeder::class,
    PostSeeder::class,
]);
```

### truncate()

Empty a table (TRUNCATE):

```php
$this->truncate('users');
```

### delete()

Delete all records (DELETE):

```php
$this->delete('users');
```

### table()

Access query builder:

```php
$this->table('users')
    ->where('email', 'like', '%@example.com')
    ->delete();
```

### db()

Access database instance:

```php
$users = $this->db()->query('SELECT * FROM users');
```

## Examples

### User Seeder

```php
<?php

use Nexus\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        $this->truncate('users');

        // Insert users
        $this->insert('users', [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('admin123'),
                'is_active' => true,
                'email_verified_at' => now()->toDateTimeString(),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
                'is_active' => true,
                'email_verified_at' => now()->toDateTimeString(),
                'created_at' => now()->subDays(10)->toDateTimeString(),
                'updated_at' => now()->subDays(10)->toDateTimeString(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => bcrypt('password'),
                'is_active' => true,
                'email_verified_at' => now()->toDateTimeString(),
                'created_at' => now()->subDays(5)->toDateTimeString(),
                'updated_at' => now()->subDays(5)->toDateTimeString(),
            ],
        ]);
    }
}
```

### Category Seeder

```php
<?php

use Nexus\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $this->truncate('categories');

        $categories = [
            'Technology',
            'Business',
            'Health',
            'Travel',
            'Food',
            'Lifestyle',
            'Education',
            'Entertainment'
        ];

        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                'name' => $category,
                'slug' => strtolower($category),
                'description' => "Articles about {$category}",
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        $this->insert('categories', $data);
    }
}
```

### Post Seeder

```php
<?php

use Nexus\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncate('posts');

        // Get users and categories
        $users = $this->table('users')->get();
        $categories = $this->table('categories')->get();

        $posts = [];

        for ($i = 1; $i <= 50; $i++) {
            $user = $users[array_rand($users)];
            $category = $categories[array_rand($categories)];

            $posts[] = [
                'user_id' => $user['id'],
                'category_id' => $category['id'],
                'title' => "Blog Post #{$i}",
                'slug' => "blog-post-{$i}",
                'content' => $this->generateContent(),
                'status' => $i <= 40 ? 'published' : 'draft',
                'views' => rand(0, 1000),
                'published_at' => $i <= 40 ? now()->subDays(rand(1, 30))->toDateTimeString() : null,
                'created_at' => now()->subDays(rand(1, 60))->toDateTimeString(),
                'updated_at' => now()->subDays(rand(0, 30))->toDateTimeString(),
            ];
        }

        $this->insert('posts', $posts);
    }

    private function generateContent(): string
    {
        return "Lorem ipsum dolor sit amet, consectetur adipiscing elit. " .
               "Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. " .
               "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.";
    }
}
```

### Comment Seeder

```php
<?php

use Nexus\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncate('comments');

        // Get published posts and users
        $posts = $this->table('posts')
            ->where('status', 'published')
            ->get();

        $users = $this->table('users')->get();

        $comments = [];

        foreach ($posts as $post) {
            // Add 3-10 comments per post
            $numComments = rand(3, 10);

            for ($i = 0; $i < $numComments; $i++) {
                $user = $users[array_rand($users)];

                $comments[] = [
                    'post_id' => $post['id'],
                    'user_id' => $user['id'],
                    'parent_id' => null,
                    'content' => "This is a comment on post {$post['title']}",
                    'is_approved' => rand(0, 10) > 2, // 80% approved
                    'created_at' => now()->subDays(rand(0, 20))->toDateTimeString(),
                    'updated_at' => now()->subDays(rand(0, 20))->toDateTimeString(),
                ];
            }
        }

        $this->insert('comments', $comments);
    }
}
```

### Product Seeder

```php
<?php

use Nexus\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncate('products');

        $products = [
            [
                'sku' => 'PROD-001',
                'name' => 'Laptop Pro 15"',
                'description' => 'Professional laptop with high performance',
                'price' => 1299.99,
                'cost' => 899.99,
                'stock' => 25,
                'is_active' => true,
            ],
            [
                'sku' => 'PROD-002',
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse',
                'price' => 29.99,
                'cost' => 15.00,
                'stock' => 150,
                'is_active' => true,
            ],
            [
                'sku' => 'PROD-003',
                'name' => 'Mechanical Keyboard',
                'description' => 'RGB mechanical gaming keyboard',
                'price' => 89.99,
                'cost' => 45.00,
                'stock' => 75,
                'is_active' => true,
            ],
        ];

        $data = [];
        foreach ($products as $product) {
            $data[] = array_merge($product, [
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]);
        }

        $this->insert('products', $data);
    }
}
```

### Master Database Seeder

```php
<?php

use Nexus\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database
     */
    public function run(): void
    {
        echo "Seeding database...\n";

        // Call seeders in order
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
            ProductSeeder::class,
        ]);

        echo "Database seeded successfully!\n";
    }
}
```

## Advanced Examples

### Seeder with Faker-like Data

```php
<?php

use Nexus\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncate('users');

        $users = [];

        for ($i = 1; $i <= 100; $i++) {
            $users[] = [
                'name' => $this->generateName(),
                'email' => "user{$i}@example.com",
                'password' => bcrypt('password'),
                'is_active' => rand(0, 10) > 1, // 90% active
                'created_at' => now()->subDays(rand(0, 365))->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];

            // Insert in batches of 50
            if ($i % 50 === 0) {
                $this->insert('users', $users);
                $users = [];
            }
        }

        // Insert remaining users
        if (!empty($users)) {
            $this->insert('users', $users);
        }
    }

    private function generateName(): string
    {
        $firstNames = ['John', 'Jane', 'Bob', 'Alice', 'Charlie', 'Diana'];
        $lastNames = ['Smith', 'Johnson', 'Brown', 'Davis', 'Wilson', 'Moore'];

        return $firstNames[array_rand($firstNames)] . ' ' .
               $lastNames[array_rand($lastNames)];
    }
}
```

### Conditional Seeding

```php
<?php

use Nexus\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if database is empty
        $userCount = $this->table('users')->count();

        if ($userCount > 0) {
            echo "Database already has data. Skipping seeding.\n";
            return;
        }

        $this->call([
            UserSeeder::class,
            PostSeeder::class,
        ]);
    }
}
```

## Best Practices

1. **Truncate Before Inserting**: Clear existing data to avoid duplicates
2. **Use Transactions**: Wrap seeders in transactions if supported
3. **Batch Inserts**: Insert multiple rows at once for better performance
4. **Test Data**: Use realistic test data that mirrors production
5. **Order Matters**: Seed parent tables before child tables
6. **Idempotent Seeders**: Make seeders safe to run multiple times
7. **Environment Check**: Consider environment when seeding sensitive data

## Common Patterns

### Seeding with Relationships

```php
public function run(): void
{
    // Seed users first
    $this->call(UserSeeder::class);

    // Get users for posts
    $users = $this->table('users')->get();

    foreach ($users as $user) {
        $posts = [];
        for ($i = 0; $i < 5; $i++) {
            $posts[] = [
                'user_id' => $user['id'],
                'title' => "Post {$i} by {$user['name']}",
                'content' => 'Content here',
                'created_at' => now()->toDateTimeString(),
            ];
        }
        $this->insert('posts', $posts);
    }
}
```

### Environment-Specific Seeding

```php
public function run(): void
{
    if (env('APP_ENV') === 'production') {
        // Only seed essential data in production
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);
    } else {
        // Seed test data in development
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            PostSeeder::class,
        ]);
    }
}
```

## Troubleshooting

### Foreign Key Constraints

If you get foreign key errors:

1. Truncate child tables before parent tables
2. Or disable foreign key checks temporarily
3. Ensure referenced IDs exist

### Memory Issues

For large datasets:

1. Insert in batches
2. Use smaller chunk sizes
3. Increase PHP memory limit

## Next Steps

- Learn about [Migrations](migrations.md)
- Understand [Models](models.md)
- Explore [Query Builder](query-builder.md)
