# Database

Nexus Framework provides a clean and simple database layer with query builder and model support.

## Configuration

Database settings are defined in `config/database.php` and can be configured via `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=root
DB_PASSWORD=secret
```

### Supported Databases

- MySQL 5.7+
- PostgreSQL 9.6+
- SQLite 3.8+

## Query Builder

The query builder provides a fluent interface for building database queries.

### Basic Queries

```php
use Nexus\Database\DB;

// Get all records
$users = DB::table('users')->get();

// Get first record
$user = DB::table('users')->first();

// Find by ID
$user = DB::table('users')->find(1);

// Count records
$count = DB::table('users')->count();
```

### Select Statements

```php
// Select specific columns
$users = DB::table('users')
    ->select(['id', 'name', 'email'])
    ->get();

// Select with alias
$users = DB::table('users')
    ->select(['id', 'name as full_name'])
    ->get();
```

### Where Clauses

```php
// Simple where
$users = DB::table('users')
    ->where('status', '=', 'active')
    ->get();

// Multiple where conditions
$users = DB::table('users')
    ->where('status', '=', 'active')
    ->where('age', '>=', 18)
    ->get();

// Or where
$users = DB::table('users')
    ->where('role', '=', 'admin')
    ->orWhere('role', '=', 'moderator')
    ->get();

// Where In
$users = DB::table('users')
    ->whereIn('id', [1, 2, 3, 4, 5])
    ->get();

// Where Null
$users = DB::table('users')
    ->whereNull('deleted_at')
    ->get();

// Where Not Null
$users = DB::table('users')
    ->whereNotNull('email_verified_at')
    ->get();
```

### Ordering

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
```

### Limit & Offset

```php
// Limit
$users = DB::table('users')
    ->limit(10)
    ->get();

// Limit with offset
$users = DB::table('users')
    ->limit(10)
    ->offset(20)
    ->get();
```

### Joins

```php
// Inner join
$users = DB::table('users')
    ->join('profiles', 'users.id', '=', 'profiles.user_id')
    ->select(['users.*', 'profiles.bio'])
    ->get();

// Left join
$users = DB::table('users')
    ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
    ->get();
```

### Group By & Having

```php
$stats = DB::table('orders')
    ->select(['user_id', 'COUNT(*) as order_count'])
    ->groupBy('user_id')
    ->having('order_count', '>', 5)
    ->get();
```

### Inserting Data

```php
// Insert single record
$id = DB::table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => date('Y-m-d H:i:s')
]);

// Insert multiple records
DB::table('users')->insert([
    ['name' => 'User 1', 'email' => 'user1@example.com'],
    ['name' => 'User 2', 'email' => 'user2@example.com'],
]);
```

### Updating Data

```php
// Update records
$affected = DB::table('users')
    ->where('id', '=', 1)
    ->update([
        'name' => 'Jane Doe',
        'updated_at' => date('Y-m-d H:i:s')
    ]);

// Update or insert
DB::table('users')
    ->where('email', '=', 'john@example.com')
    ->updateOrInsert([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
```

### Deleting Data

```php
// Delete by ID
DB::table('users')->delete(1);

// Delete with condition
DB::table('users')
    ->where('status', '=', 'inactive')
    ->delete();

// Delete all records
DB::table('users')->delete();
```

### Raw Queries

```php
// Raw select
$users = DB::raw('SELECT * FROM users WHERE age > ?', [18]);

// Raw statement
DB::statement('ALTER TABLE users ADD COLUMN status VARCHAR(20)');
```

## Models

Models provide an elegant way to interact with database tables.

### Creating Models

```bash
php nexus make:model User
php nexus make:model Post --table=blog_posts
```

### Basic Model

```php
<?php

namespace App\Models;

use Nexus\Database\Model;

class User extends Model
{
    protected static ?string $table = 'users';
    protected static ?string $primaryKey = 'id';
}
```

### Querying Models

```php
// Get all records
$users = User::all();

// Find by ID
$user = User::find(1);

// Where clause
$users = User::where('status', 'active')->get();

// Find first matching
$user = User::where('email', 'john@example.com')->first();

// Count records
$count = User::count();
```

### Creating Records

```php
// Create new record
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_BCRYPT)
]);

// Or using new instance
$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->save();
```

### Updating Records

```php
// Update existing record
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();

// Or use update method
$user = User::find(1);
$user->update([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com'
]);
```

### Deleting Records

```php
// Delete record
$user = User::find(1);
$user->delete();

// Delete by ID
User::destroy(1);

// Delete multiple
User::destroy([1, 2, 3]);
```

## Complete Example

```php
<?php

namespace App\Controllers;

use App\Models\User;
use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Route\{Get, Post, Put, Delete};

class UserController
{
    #[Get('/users', 'users.index')]
    public function index(Request $request): Response
    {
        $users = User::all();
        return Response::json($users);
    }

    #[Post('/users', 'users.store')]
    public function store(Request $request): Response
    {
        $validated = validate($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $validated['password'] = password_hash($validated['password'], PASSWORD_BCRYPT);
        $user = User::create($validated);

        return Response::json($user, 201);
    }

    #[Get('/users/{id}', 'users.show')]
    public function show(Request $request, int $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }

        return Response::json($user);
    }

    #[Put('/users/{id}', 'users.update')]
    public function update(Request $request, int $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }

        $validated = validate($request->all(), [
            'name' => 'string|max:255',
            'email' => "email|unique:users,email,{$id}"
        ]);

        $user->update($validated);

        return Response::json($user);
    }

    #[Delete('/users/{id}', 'users.destroy')]
    public function destroy(Request $request, int $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }

        $user->delete();

        return Response::json(['message' => 'User deleted successfully']);
    }
}
```

## Best Practices

1. **Use Models**: For standard CRUD operations
2. **Query Builder**: For complex queries
3. **Prepare Statements**: Always use parameter binding
4. **Transactions**: Use for data consistency
5. **Indexes**: Index frequently queried columns
6. **Validation**: Validate data before inserting
7. **Sanitization**: Clean user input
8. **Error Handling**: Handle database errors gracefully

## Next Steps

- Learn about [Models](models.md)
- Understand [Query Builder](query-builder.md)
- Explore [Validation](validation.md)
