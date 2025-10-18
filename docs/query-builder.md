# Query Builder

The Nexus Query Builder provides a fluent interface for building and executing database queries. It offers protection against SQL injection while keeping queries readable and maintainable.

## Table of Contents

- [Basic Usage](#basic-usage)
- [Selecting Data](#selecting-data)
- [Where Clauses](#where-clauses)
- [Ordering & Limiting](#ordering--limiting)
- [Joins](#joins)
- [Aggregates](#aggregates)
- [Inserting Data](#inserting-data)
- [Updating Data](#updating-data)
- [Deleting Data](#deleting-data)
- [Raw Expressions](#raw-expressions)

## Basic Usage

### Accessing the Query Builder

```php
use Nexus\Database\DB;

// Get all records
$users = DB::table('users')->get();

// Get first record
$user = DB::table('users')->first();

// Find by ID
$user = DB::table('users')->find(1);
```

## Selecting Data

### Select All Columns

```php
// Get all columns
$users = DB::table('users')->get();
```

### Select Specific Columns

```php
// Select specific columns
$users = DB::table('users')
    ->select(['id', 'name', 'email'])
    ->get();

// Or use multiple select calls
$users = DB::table('users')
    ->select(['id', 'name'])
    ->select(['email'])
    ->get();
```

### Column Aliases

```php
// Select with alias
$users = DB::table('users')
    ->select(['id', 'name as full_name', 'email as user_email'])
    ->get();
```

### Get First Record

```php
// Get first record
$user = DB::table('users')
    ->where('email', '=', 'john@example.com')
    ->first();
```

### Find by ID

```php
// Find by primary key
$user = DB::table('users')->find(1);

// Equivalent to:
$user = DB::table('users')
    ->where('id', '=', 1)
    ->first();
```

## Where Clauses

### Basic Where

```php
// Single where clause
$users = DB::table('users')
    ->where('status', '=', 'active')
    ->get();

// Multiple where clauses (AND)
$users = DB::table('users')
    ->where('status', '=', 'active')
    ->where('age', '>=', 18)
    ->get();
```

### Comparison Operators

```php
// Equals
$users = DB::table('users')->where('status', '=', 'active')->get();

// Not equals
$users = DB::table('users')->where('status', '!=', 'banned')->get();

// Greater than
$users = DB::table('users')->where('age', '>', 18)->get();

// Greater than or equal
$users = DB::table('users')->where('age', '>=', 18)->get();

// Less than
$users = DB::table('users')->where('credits', '<', 100)->get();

// Less than or equal
$users = DB::table('users')->where('credits', '<=', 50)->get();

// LIKE
$users = DB::table('users')
    ->where('name', 'LIKE', 'John%')
    ->get();
```

### Or Where

```php
// OR conditions
$users = DB::table('users')
    ->where('role', '=', 'admin')
    ->orWhere('role', '=', 'moderator')
    ->get();

// Equivalent to: WHERE role = 'admin' OR role = 'moderator'
```

### Where In

```php
// WHERE IN
$users = DB::table('users')
    ->whereIn('id', [1, 2, 3, 4, 5])
    ->get();

// Equivalent to: WHERE id IN (1, 2, 3, 4, 5)

// WHERE NOT IN
$users = DB::table('users')
    ->whereNotIn('status', ['banned', 'suspended'])
    ->get();
```

### Where Null

```php
// WHERE IS NULL
$users = DB::table('users')
    ->whereNull('deleted_at')
    ->get();

// WHERE IS NOT NULL
$users = DB::table('users')
    ->whereNotNull('email_verified_at')
    ->get();
```

### Where Between

```php
// WHERE BETWEEN
$users = DB::table('users')
    ->whereBetween('age', 18, 65)
    ->get();

// Equivalent to: WHERE age BETWEEN 18 AND 65
```

## Ordering & Limiting

### Order By

```php
// Order by single column
$users = DB::table('users')
    ->orderBy('name', 'ASC')
    ->get();

// Order by multiple columns
$users = DB::table('users')
    ->orderBy('status', 'DESC')
    ->orderBy('name', 'ASC')
    ->get();

// Order descending
$users = DB::table('users')
    ->orderBy('created_at', 'DESC')
    ->get();
```

### Limit

```php
// Limit results
$users = DB::table('users')
    ->limit(10)
    ->get();
```

### Offset

```php
// Offset results
$users = DB::table('users')
    ->limit(10)
    ->offset(20)
    ->get();

// Equivalent to: LIMIT 10 OFFSET 20
```

### Pagination

```php
// Paginate results
$page = 1;
$perPage = 15;

$users = DB::table('users')
    ->limit($perPage)
    ->offset(($page - 1) * $perPage)
    ->get();
```

## Joins

### Inner Join

```php
// Inner join
$users = DB::table('users')
    ->join('profiles', 'users.id', '=', 'profiles.user_id')
    ->select(['users.*', 'profiles.bio', 'profiles.avatar'])
    ->get();
```

### Left Join

```php
// Left join
$users = DB::table('users')
    ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
    ->select(['users.*', 'profiles.bio'])
    ->get();
```

### Multiple Joins

```php
// Multiple joins
$posts = DB::table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->join('categories', 'posts.category_id', '=', 'categories.id')
    ->select([
        'posts.*',
        'users.name as author_name',
        'categories.name as category_name'
    ])
    ->get();
```

## Aggregates

### Count

```php
// Count all records
$count = DB::table('users')->count();

// Count with condition
$count = DB::table('users')
    ->where('status', '=', 'active')
    ->count();
```

### Sum

```php
// Sum column
$total = DB::table('orders')
    ->sum('amount');

// Sum with condition
$total = DB::table('orders')
    ->where('status', '=', 'completed')
    ->sum('amount');
```

### Average

```php
// Average
$average = DB::table('products')
    ->avg('price');
```

### Min & Max

```php
// Minimum value
$minPrice = DB::table('products')->min('price');

// Maximum value
$maxPrice = DB::table('products')->max('price');
```

## Group By & Having

### Group By

```php
// Group by single column
$stats = DB::table('orders')
    ->select(['user_id', 'COUNT(*) as order_count'])
    ->groupBy('user_id')
    ->get();

// Group by multiple columns
$stats = DB::table('sales')
    ->select(['year', 'month', 'SUM(amount) as total'])
    ->groupBy('year', 'month')
    ->get();
```

### Having

```php
// Having clause
$stats = DB::table('orders')
    ->select(['user_id', 'COUNT(*) as order_count'])
    ->groupBy('user_id')
    ->having('order_count', '>', 5)
    ->get();
```

## Inserting Data

### Insert Single Record

```php
// Insert single record
$userId = DB::table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_BCRYPT),
    'created_at' => date('Y-m-d H:i:s')
]);

// Returns the inserted ID
echo "Inserted user ID: {$userId}";
```

### Insert Multiple Records

```php
// Insert multiple records
DB::table('users')->insert([
    [
        'name' => 'User 1',
        'email' => 'user1@example.com',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'name' => 'User 2',
        'email' => 'user2@example.com',
        'created_at' => date('Y-m-d H:i:s')
    ]
]);
```

## Updating Data

### Update Records

```php
// Update records
$affected = DB::table('users')
    ->where('id', '=', 1)
    ->update([
        'name' => 'Jane Doe',
        'updated_at' => date('Y-m-d H:i:s')
    ]);

// Returns number of affected rows
echo "Updated {$affected} records";
```

### Update Multiple Conditions

```php
// Update with multiple conditions
$affected = DB::table('users')
    ->where('status', '=', 'inactive')
    ->where('last_login', '<', date('Y-m-d', strtotime('-30 days')))
    ->update([
        'status' => 'archived',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
```

### Increment & Decrement

```php
// Increment a column
DB::table('users')
    ->where('id', '=', 1)
    ->increment('login_count');

// Decrement a column
DB::table('products')
    ->where('id', '=', 1)
    ->decrement('stock');

// Increment by amount
DB::table('users')
    ->where('id', '=', 1)
    ->increment('credits', 100);
```

## Deleting Data

### Delete Records

```php
// Delete by ID
DB::table('users')->delete(1);

// Delete with condition
$deleted = DB::table('users')
    ->where('status', '=', 'inactive')
    ->delete();

// Returns number of deleted rows
echo "Deleted {$deleted} records";
```

### Delete All Records

```php
// Delete all records (use with caution!)
DB::table('temp_data')->delete();
```

## Raw Expressions

### Raw Queries

```php
// Raw select query
$users = DB::raw('SELECT * FROM users WHERE age > ?', [18]);

// Raw query with named parameters
$users = DB::raw(
    'SELECT * FROM users WHERE status = ? AND age > ?',
    ['active', 18]
);
```

### Raw Statements

```php
// Execute raw statement
DB::statement('ALTER TABLE users ADD COLUMN last_login TIMESTAMP');

// Create table
DB::statement('
    CREATE TABLE sessions (
        id VARCHAR(255) PRIMARY KEY,
        user_id INT,
        payload TEXT,
        last_activity INT
    )
');
```

## Complex Query Examples

### Subqueries

```php
// Subquery in WHERE
$recentUsers = DB::raw('
    SELECT * FROM users
    WHERE id IN (
        SELECT user_id FROM orders
        WHERE created_at > ?
    )
', [date('Y-m-d', strtotime('-7 days'))]);
```

### Advanced Joins with Aggregates

```php
// Users with post counts
$users = DB::table('users')
    ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->select([
        'users.id',
        'users.name',
        'COUNT(posts.id) as post_count'
    ])
    ->groupBy('users.id', 'users.name')
    ->having('post_count', '>', 0)
    ->orderBy('post_count', 'DESC')
    ->get();
```

### Search with Multiple Conditions

```php
// Search users
$search = 'john';
$users = DB::table('users')
    ->where('name', 'LIKE', "%{$search}%")
    ->orWhere('email', 'LIKE', "%{$search}%")
    ->where('status', '=', 'active')
    ->orderBy('name', 'ASC')
    ->limit(50)
    ->get();
```

## Transaction Support

```php
// Begin transaction
DB::beginTransaction();

try {
    // Insert user
    $userId = DB::table('users')->insert([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);

    // Insert profile
    DB::table('profiles')->insert([
        'user_id' => $userId,
        'bio' => 'Software developer'
    ]);

    // Commit transaction
    DB::commit();

} catch (\Exception $e) {
    // Rollback on error
    DB::rollback();
    throw $e;
}
```

## Complete Example

```php
<?php

namespace App\Controllers;

use Nexus\Database\DB;
use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Route\Get;

class ReportController
{
    #[Get('/reports/users', 'reports.users')]
    public function userReport(Request $request): Response
    {
        // Get query parameters
        $status = $request->query('status', 'active');
        $orderBy = $request->query('order_by', 'name');
        $direction = $request->query('direction', 'ASC');
        $page = $request->query('page', 1);
        $perPage = 20;

        // Build query
        $query = DB::table('users')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.status',
                'users.created_at',
                'COUNT(posts.id) as post_count'
            ])
            ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.status', 'users.created_at');

        // Apply filters
        if ($status !== 'all') {
            $query->where('users.status', '=', $status);
        }

        // Apply ordering
        $query->orderBy($orderBy, $direction);

        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $users = $query
            ->limit($perPage)
            ->offset($offset)
            ->get();

        // Get total count
        $total = DB::table('users')
            ->where('status', '=', $status)
            ->count();

        return Response::json([
            'data' => $users,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage)
            ]
        ]);
    }
}
```

## Best Practices

1. **Use Parameter Binding**: Always use placeholders for values
2. **Select Specific Columns**: Don't use SELECT * in production
3. **Index Columns**: Index frequently queried columns
4. **Limit Results**: Always use LIMIT for large datasets
5. **Use Transactions**: For related operations
6. **Avoid N+1 Queries**: Use joins instead of loops
7. **Cache Results**: Cache expensive queries
8. **Monitor Performance**: Log slow queries

## Next Steps

- Learn about [Models](models.md)
- Understand [Database](database.md)
- Explore [Validation](validation.md)
