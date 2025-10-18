# Models

Models provide an elegant Active Record implementation for working with your database. Each database table has a corresponding Model which is used to interact with that table.

## Table of Contents

- [Creating Models](#creating-models)
- [Model Conventions](#model-conventions)
- [Retrieving Models](#retrieving-models)
- [Inserting & Updating](#inserting--updating)
- [Deleting Models](#deleting-models)
- [Query Scopes](#query-scopes)
- [Relationships](#relationships)
- [Mass Assignment](#mass-assignment)

## Creating Models

### Using CLI (Recommended)

Generate a model using the Artisan command:

```bash
# Basic model
php nexus make:model User

# Model with custom table name
php nexus make:model Product --table=products_catalog

# Create model and controller together
php nexus make:module Post
```

### Manual Creation

Create a model file in `app/Models/`:

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

## Model Conventions

### Table Names

By default, models assume the table name is the plural, snake_case version of the class name:

```php
User        → users
Post        → posts
Product     → products
OrderItem   → order_items
```

Override the table name:

```php
class Product extends Model
{
    protected static ?string $table = 'products_catalog';
}
```

### Primary Keys

By default, models assume the primary key is named `id`. Override if different:

```php
class User extends Model
{
    protected static ?string $primaryKey = 'user_id';
}
```

## Retrieving Models

### Get All Records

```php
// Get all users
$users = User::all();

// Iterate over results
foreach ($users as $user) {
    echo $user->name;
}
```

### Find by ID

```php
// Find by primary key
$user = User::find(1);

if ($user) {
    echo $user->name;
}
```

### Find with Exception

```php
// Find or throw exception
try {
    $user = User::findOrFail(1);
} catch (\Exception $e) {
    // Handle not found
}
```

### Where Clauses

```php
// Simple where
$users = User::where('status', 'active')->get();

// Multiple conditions
$users = User::where('status', 'active')
    ->where('age', '>=', 18)
    ->get();

// OR conditions
$users = User::where('role', 'admin')
    ->orWhere('role', 'moderator')
    ->get();
```

### Find First

```php
// Get first matching record
$user = User::where('email', 'john@example.com')->first();

if ($user) {
    echo $user->name;
}
```

### Ordering

```php
// Order by column
$users = User::orderBy('name', 'ASC')->get();

// Multiple order by
$users = User::orderBy('status', 'DESC')
    ->orderBy('name', 'ASC')
    ->get();
```

### Limiting Results

```php
// Limit results
$users = User::limit(10)->get();

// Limit with offset
$users = User::limit(10)->offset(20)->get();
```

### Counting

```php
// Count all records
$count = User::count();

// Count with condition
$activeCount = User::where('status', 'active')->count();
```

## Inserting & Updating

### Creating Records

```php
// Method 1: Using create()
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_BCRYPT)
]);

echo "Created user ID: {$user->id}";

// Method 2: Using new instance
$user = new User();
$user->name = 'Jane Doe';
$user->email = 'jane@example.com';
$user->password = password_hash('secret', PASSWORD_BCRYPT);
$user->save();
```

### Updating Records

```php
// Method 1: Find and update
$user = User::find(1);
$user->name = 'Updated Name';
$user->save();

// Method 2: Using update()
$user = User::find(1);
$user->update([
    'name' => 'Updated Name',
    'email' => 'newemail@example.com'
]);

// Method 3: Update without retrieving
User::where('id', 1)->update([
    'name' => 'Updated Name'
]);
```

### Save Method

```php
// Save creates or updates
$user = new User();
$user->name = 'John';
$user->email = 'john@example.com';
$user->save(); // INSERT

$user->name = 'Jane';
$user->save(); // UPDATE
```

## Deleting Models

### Delete Instance

```php
// Delete instance
$user = User::find(1);
$user->delete();
```

### Delete by ID

```php
// Delete by ID
User::destroy(1);

// Delete multiple IDs
User::destroy([1, 2, 3, 4, 5]);
```

### Delete with Condition

```php
// Delete where condition
User::where('status', 'inactive')->delete();
```

## Accessing Properties

### Get Attributes

```php
$user = User::find(1);

// Access as properties
echo $user->name;
echo $user->email;

// Access as array
echo $user['name'];
echo $user['email'];

// Get all attributes
$attributes = $user->toArray();
```

### Set Attributes

```php
$user = User::find(1);

// Set as properties
$user->name = 'New Name';
$user->email = 'new@example.com';

// Save changes
$user->save();
```

## Mass Assignment

### Fillable Attributes

Protect against mass assignment vulnerabilities:

```php
class User extends Model
{
    protected array $fillable = [
        'name',
        'email',
        'password'
    ];
}

// Now you can use create()
$user = User::create($request->all());
```

### Guarded Attributes

Specify which attributes should NOT be mass assignable:

```php
class User extends Model
{
    protected array $guarded = [
        'id',
        'role',
        'is_admin'
    ];
}
```

## Query Scopes

### Local Scopes

Define reusable query constraints:

```php
class User extends Model
{
    public static function active()
    {
        return static::where('status', 'active');
    }

    public static function admins()
    {
        return static::where('role', 'admin');
    }
}

// Usage
$activeUsers = User::active()->get();
$adminUsers = User::admins()->get();

// Chain scopes
$activeAdmins = User::active()->admins()->get();
```

## Timestamps

### Automatic Timestamps

Enable automatic `created_at` and `updated_at` timestamps:

```php
class User extends Model
{
    protected bool $timestamps = true;
}

// Timestamps automatically managed
$user = User::create([
    'name' => 'John',
    'email' => 'john@example.com'
]);
// created_at and updated_at automatically set
```

### Disable Timestamps

```php
class User extends Model
{
    protected bool $timestamps = false;
}
```

## Complete Model Example

```php
<?php

namespace App\Models;

use Nexus\Database\Model;

class User extends Model
{
    /**
     * The table associated with the model
     */
    protected static ?string $table = 'users';

    /**
     * The primary key for the model
     */
    protected static ?string $primaryKey = 'id';

    /**
     * Enable timestamps
     */
    protected bool $timestamps = true;

    /**
     * The attributes that are mass assignable
     */
    protected array $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'avatar'
    ];

    /**
     * The attributes that should be hidden
     */
    protected array $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * Scope: Get active users
     */
    public static function active()
    {
        return static::where('status', 'active');
    }

    /**
     * Scope: Get admin users
     */
    public static function admins()
    {
        return static::where('role', 'admin');
    }

    /**
     * Scope: Get verified users
     */
    public static function verified()
    {
        return static::whereNotNull('email_verified_at');
    }

    /**
     * Hash password before saving
     */
    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return $this->name;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if email is verified
     */
    public function isVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }
}
```

## Using Models in Controllers

### CRUD Operations

```php
<?php

namespace App\Controllers;

use App\Models\User;
use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Route\{Get, Post, Put, Delete};

class UserController
{
    /**
     * List all users
     */
    #[Get('/users', 'users.index')]
    public function index(Request $request): Response
    {
        $users = User::orderBy('name', 'ASC')->get();

        return Response::json($users);
    }

    /**
     * Create new user
     */
    #[Post('/users', 'users.store')]
    public function store(Request $request): Response
    {
        $validated = validate($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->setPassword($validated['password']);
        $user->save();

        return Response::json($user, 201);
    }

    /**
     * Show single user
     */
    #[Get('/users/{id}', 'users.show')]
    public function show(Request $request, int $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json([
                'error' => 'User not found'
            ], 404);
        }

        return Response::json($user);
    }

    /**
     * Update user
     */
    #[Put('/users/{id}', 'users.update')]
    public function update(Request $request, int $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json([
                'error' => 'User not found'
            ], 404);
        }

        $validated = validate($request->all(), [
            'name' => 'string|max:255',
            'email' => "email|unique:users,email,{$id}"
        ]);

        $user->update($validated);

        return Response::json($user);
    }

    /**
     * Delete user
     */
    #[Delete('/users/{id}', 'users.destroy')]
    public function destroy(Request $request, int $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json([
                'error' => 'User not found'
            ], 404);
        }

        $user->delete();

        return Response::json([
            'message' => 'User deleted successfully'
        ]);
    }
}
```

## Advanced Queries

### Complex Where Clauses

```php
// Multiple conditions
$users = User::where('status', 'active')
    ->where('age', '>=', 18)
    ->where('country', 'US')
    ->get();

// Where in
$users = User::whereIn('id', [1, 2, 3, 4])->get();

// Where null
$users = User::whereNull('deleted_at')->get();

// Where not null
$users = User::whereNotNull('email_verified_at')->get();
```

### Ordering and Limiting

```php
// Top 10 users
$users = User::orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

// Pagination
$page = 1;
$perPage = 15;
$users = User::limit($perPage)
    ->offset(($page - 1) * $perPage)
    ->get();
```

### Aggregates

```php
// Count
$totalUsers = User::count();
$activeUsers = User::where('status', 'active')->count();

// Custom queries
$users = User::where('role', 'admin')
    ->orderBy('name', 'ASC')
    ->get();
```

## Best Practices

1. **Use Mass Assignment Protection**: Define fillable or guarded
2. **Validate Input**: Always validate before creating/updating
3. **Hash Passwords**: Use password_hash() for passwords
4. **Use Scopes**: Create reusable query methods
5. **Type Hints**: Use proper return type hints
6. **Handle Not Found**: Check if model exists before using
7. **Transactions**: Use for related operations
8. **Eager Loading**: Prevent N+1 query problems

## Next Steps

- Learn about [Query Builder](query-builder.md)
- Understand [Database](database.md)
- Explore [Validation](validation.md)
- Work with [Controllers](controllers.md)
