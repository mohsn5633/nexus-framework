# Routing

Nexus Framework provides a powerful and flexible routing system with two approaches: attribute-based routing (recommended) and file-based routing.

## Table of Contents

- [Attribute-Based Routing](#attribute-based-routing)
- [File-Based Routing](#file-based-routing)
- [Route Parameters](#route-parameters)
- [Route Names](#route-names)
- [Route Groups](#route-groups)
- [Route Middleware](#route-middleware)
- [Available HTTP Methods](#available-http-methods)

## Attribute-Based Routing

The recommended way to define routes is using PHP 8 attributes directly on controller methods.

### Basic Routes

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Route\Get;
use Nexus\Http\Route\Post;

class HomeController
{
    #[Get('/', 'home')]
    public function index(Request $request): Response
    {
        return Response::view('welcome');
    }

    #[Get('/about', 'about')]
    public function about(Request $request): Response
    {
        return Response::view('about');
    }

    #[Post('/contact', 'contact.submit')]
    public function contact(Request $request): Response
    {
        // Handle contact form
        return Response::json(['success' => true]);
    }
}
```

### Route Discovery

Routes are automatically discovered from all controllers in `app/Controllers/` directory during application boot. No manual registration required!

## File-Based Routing

You can also define routes in `bootstrap/routes.php`:

```php
<?php

use Nexus\Http\Router;
use App\Controllers\HomeController;

/** @var Router $router */

$router->get('/', [HomeController::class, 'index'])->name('home');
$router->post('/contact', [HomeController::class, 'contact'])->name('contact.submit');

// Closure routes
$router->get('/hello', function () {
    return Response::json(['message' => 'Hello World!']);
});
```

## Available HTTP Methods

Nexus supports all standard HTTP methods:

### Using Attributes

```php
use Nexus\Http\Route\{Get, Post, Put, Patch, Delete, Options};

class UserController
{
    #[Get('/users', 'users.index')]
    public function index() { }

    #[Post('/users', 'users.store')]
    public function store() { }

    #[Get('/users/{id}', 'users.show')]
    public function show() { }

    #[Put('/users/{id}', 'users.update')]
    public function update() { }

    #[Patch('/users/{id}', 'users.patch')]
    public function patch() { }

    #[Delete('/users/{id}', 'users.destroy')]
    public function destroy() { }
}
```

### Using File-Based Routes

```php
$router->get('/users', [UserController::class, 'index']);
$router->post('/users', [UserController::class, 'store']);
$router->put('/users/{id}', [UserController::class, 'update']);
$router->patch('/users/{id}', [UserController::class, 'patch']);
$router->delete('/users/{id}', [UserController::class, 'destroy']);
```

## Route Parameters

### Required Parameters

```php
#[Get('/users/{id}', 'users.show')]
public function show(Request $request, int $id): Response
{
    $user = User::find($id);
    return Response::json($user);
}

#[Get('/posts/{postId}/comments/{commentId}', 'comments.show')]
public function showComment(Request $request, int $postId, int $commentId): Response
{
    // Access parameters
    return Response::json([
        'post_id' => $postId,
        'comment_id' => $commentId
    ]);
}
```

### Accessing Parameters

Parameters are automatically injected into controller methods:

```php
#[Get('/users/{id}', 'users.show')]
public function show(Request $request, int $id): Response
{
    // Method 1: Via parameter injection (recommended)
    $userId = $id;

    // Method 2: Via request object
    $userId = $request->route('id');

    return Response::json(['id' => $userId]);
}
```

### Multiple Parameters

```php
#[Get('/categories/{category}/products/{product}', 'products.show')]
public function show(Request $request, string $category, int $product): Response
{
    return Response::json([
        'category' => $category,
        'product' => $product
    ]);
}
```

## Route Names

Named routes allow you to easily generate URLs and redirects.

### Defining Named Routes

```php
// With attributes (second parameter)
#[Get('/users/{id}', 'users.show')]
public function show(Request $request, int $id): Response { }

// With file-based routing
$router->get('/users/{id}', [UserController::class, 'show'])
    ->name('users.show');
```

### Using Named Routes

```php
// Generate URL
$url = route('users.show', ['id' => 123]);
// Output: /users/123

// Redirect to named route
return Response::redirect(route('users.show', ['id' => 123]));
```

## Route Groups

Group routes with common attributes like prefixes and middleware.

### Prefix Groups

```php
$router->group(['prefix' => '/api'], function ($router) {
    $router->get('/users', [UserController::class, 'index']);
    $router->get('/posts', [PostController::class, 'index']);
});

// Routes: /api/users, /api/posts
```

### Middleware Groups

```php
$router->group(['middleware' => ['auth']], function ($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->get('/profile', [ProfileController::class, 'show']);
});
```

### Combined Groups

```php
$router->group([
    'prefix' => '/api/v1',
    'middleware' => ['auth', 'api']
], function ($router) {
    $router->get('/users', [UserController::class, 'index']);
    $router->post('/users', [UserController::class, 'store']);
});

// Routes: /api/v1/users with auth and api middleware
```

### Nested Groups

```php
$router->group(['prefix' => '/api'], function ($router) {
    $router->group(['prefix' => '/v1'], function ($router) {
        $router->get('/users', [UserController::class, 'index']);
        // Route: /api/v1/users
    });

    $router->group(['prefix' => '/v2'], function ($router) {
        $router->get('/users', [UserControllerV2::class, 'index']);
        // Route: /api/v2/users
    });
});
```

## Route Middleware

Apply middleware to filter requests before they reach your controller.

### Single Middleware

```php
// With attributes
#[Get('/admin', 'admin.dashboard')]
#[Middleware('auth')]
public function dashboard(Request $request): Response { }

// With file-based routing
$router->get('/admin', [AdminController::class, 'dashboard'])
    ->middleware('auth');
```

### Multiple Middleware

```php
// With attributes
#[Get('/admin/users', 'admin.users')]
#[Middleware(['auth', 'admin'])]
public function users(Request $request): Response { }

// With file-based routing
$router->get('/admin/users', [AdminController::class, 'users'])
    ->middleware(['auth', 'admin']);
```

### Middleware on Groups

```php
$router->group(['middleware' => ['auth', 'verified']], function ($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->get('/settings', [SettingsController::class, 'index']);
});
```

## Resource Routes

Create RESTful resource routes quickly:

```php
// Generates all CRUD routes
$router->resource('/posts', PostController::class);

// Equivalent to:
// GET    /posts           -> index
// POST   /posts           -> store
// GET    /posts/{id}      -> show
// PUT    /posts/{id}      -> update
// DELETE /posts/{id}      -> destroy
```

## Fallback Routes

Define a fallback route for 404 errors:

```php
$router->fallback(function () {
    return Response::view('errors.404', [], 404);
});
```

## Viewing Routes

List all registered routes:

```bash
php nexus routes:list
```

Output:
```
+--------+-----------------+-------------------+
| Method | URI             | Name              |
+--------+-----------------+-------------------+
| GET    | /               | home              |
| GET    | /users          | users.index       |
| POST   | /users          | users.store       |
| GET    | /users/{id}     | users.show        |
| PUT    | /users/{id}     | users.update      |
| DELETE | /users/{id}     | users.destroy     |
+--------+-----------------+-------------------+
```

## Advanced Examples

### API Routes

```php
<?php

namespace App\Controllers\Api;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Route\{Get, Post, Put, Delete};
use Nexus\Http\Route\Middleware;

#[Middleware(['api', 'auth:api'])]
class ApiUserController
{
    #[Get('/api/v1/users', 'api.users.index')]
    public function index(Request $request): Response
    {
        $users = User::all();
        return Response::json($users);
    }

    #[Post('/api/v1/users', 'api.users.store')]
    public function store(Request $request): Response
    {
        $validated = validate($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users'
        ]);

        $user = User::create($validated);
        return Response::json($user, 201);
    }

    #[Get('/api/v1/users/{id}', 'api.users.show')]
    public function show(Request $request, int $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }

        return Response::json($user);
    }

    #[Put('/api/v1/users/{id}', 'api.users.update')]
    public function update(Request $request, int $id): Response
    {
        // Update logic
    }

    #[Delete('/api/v1/users/{id}', 'api.users.destroy')]
    public function destroy(Request $request, int $id): Response
    {
        // Delete logic
    }
}
```

## Best Practices

1. **Use Attribute Routing**: More maintainable and discoverable
2. **Name Your Routes**: Makes URL generation easier
3. **Group Related Routes**: Keep routes organized with prefixes
4. **Use Resource Routes**: For standard CRUD operations
5. **Type-Hint Parameters**: Use proper types for route parameters
6. **Apply Middleware**: Protect routes with authentication/authorization
7. **RESTful Conventions**: Follow REST principles for API routes

## Next Steps

- Learn about [Controllers](controllers.md)
- Understand [Middleware](middleware.md)
- Explore [Request & Response](request-response.md)
