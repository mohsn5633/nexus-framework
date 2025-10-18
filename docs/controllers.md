# Controllers

Controllers organize your request handling logic into reusable classes. Instead of defining all request handling logic in routes, you can organize this behavior using controller classes.

## Table of Contents

- [Creating Controllers](#creating-controllers)
- [Basic Controllers](#basic-controllers)
- [Resource Controllers](#resource-controllers)
- [Dependency Injection](#dependency-injection)
- [Controller Middleware](#controller-middleware)
- [Best Practices](#best-practices)

## Creating Controllers

### Using CLI (Recommended)

Generate a controller using the Artisan command:

```bash
# Basic controller
php nexus make:controller UserController

# Resource controller (with CRUD methods)
php nexus make:controller PostController --resource
```

### Manual Creation

Create a file in `app/Controllers/`:

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;

class UserController
{
    public function index(Request $request): Response
    {
        return Response::json(['message' => 'User list']);
    }
}
```

## Basic Controllers

### Simple Controller

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
        $data = $request->all();

        // Process contact form
        // Send email, save to database, etc.

        return Response::json([
            'success' => true,
            'message' => 'Thank you for contacting us!'
        ]);
    }
}
```

### Returning Responses

Controllers can return different types of responses:

```php
class ResponseController
{
    // JSON Response
    #[Get('/api/users', 'api.users')]
    public function jsonResponse(): Response
    {
        return Response::json([
            'users' => ['John', 'Jane', 'Bob']
        ]);
    }

    // View Response
    #[Get('/dashboard', 'dashboard')]
    public function viewResponse(): Response
    {
        return Response::view('dashboard', [
            'title' => 'Dashboard'
        ]);
    }

    // Redirect Response
    #[Post('/logout', 'logout')]
    public function redirectResponse(): Response
    {
        // Logout logic
        return Response::redirect('/login');
    }

    // Plain Text Response
    #[Get('/health', 'health')]
    public function textResponse(): Response
    {
        return Response::text('OK', 200);
    }

    // Custom Status Code
    #[Get('/not-found', 'not.found')]
    public function customStatus(): Response
    {
        return Response::json(['error' => 'Not found'], 404);
    }
}
```

## Resource Controllers

Resource controllers follow RESTful conventions for CRUD operations.

### Creating Resource Controller

```bash
php nexus make:controller PostController --resource
```

### Generated Structure

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Route\{Get, Post, Put, Delete};

class PostController
{
    /**
     * Display a listing of the resource
     */
    #[Get('/posts', 'posts.index')]
    public function index(Request $request): Response
    {
        $posts = Post::all();
        return Response::json($posts);
    }

    /**
     * Store a newly created resource
     */
    #[Post('/posts', 'posts.store')]
    public function store(Request $request): Response
    {
        $validated = validate($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        $post = Post::create($validated);
        return Response::json($post, 201);
    }

    /**
     * Display the specified resource
     */
    #[Get('/posts/{id}', 'posts.show')]
    public function show(Request $request, int $id): Response
    {
        $post = Post::find($id);

        if (!$post) {
            return Response::json(['error' => 'Post not found'], 404);
        }

        return Response::json($post);
    }

    /**
     * Update the specified resource
     */
    #[Put('/posts/{id}', 'posts.update')]
    public function update(Request $request, int $id): Response
    {
        $post = Post::find($id);

        if (!$post) {
            return Response::json(['error' => 'Post not found'], 404);
        }

        $validated = validate($request->all(), [
            'title' => 'string|max:255',
            'content' => 'string'
        ]);

        $post->update($validated);
        return Response::json($post);
    }

    /**
     * Remove the specified resource
     */
    #[Delete('/posts/{id}', 'posts.destroy')]
    public function destroy(Request $request, int $id): Response
    {
        $post = Post::find($id);

        if (!$post) {
            return Response::json(['error' => 'Post not found'], 404);
        }

        $post->delete();
        return Response::json(['message' => 'Post deleted successfully']);
    }
}
```

## Dependency Injection

Controllers support automatic dependency injection through constructor or method parameters.

### Constructor Injection

```php
<?php

namespace App\Controllers;

use Nexus\Database\Database;
use Nexus\Http\Request;
use Nexus\Http\Response;

class UserController
{
    public function __construct(
        protected Database $db
    ) {
    }

    #[Get('/users', 'users.index')]
    public function index(Request $request): Response
    {
        $users = $this->db->table('users')->get();
        return Response::json($users);
    }
}
```

### Method Injection

```php
class PostController
{
    #[Get('/posts', 'posts.index')]
    public function index(Request $request, Database $db): Response
    {
        $posts = $db->table('posts')->get();
        return Response::json($posts);
    }
}
```

### Custom Service Injection

```php
<?php

namespace App\Controllers;

use App\Services\UserService;
use Nexus\Http\Request;
use Nexus\Http\Response;

class UserController
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    #[Get('/users', 'users.index')]
    public function index(Request $request): Response
    {
        $users = $this->userService->getAllUsers();
        return Response::json($users);
    }

    #[Post('/users', 'users.store')]
    public function store(Request $request): Response
    {
        $user = $this->userService->createUser($request->all());
        return Response::json($user, 201);
    }
}
```

## Controller Middleware

Apply middleware to specific controller actions or entire controllers.

### Method-Level Middleware

```php
<?php

namespace App\Controllers;

use Nexus\Http\Route\{Get, Post, Middleware};

class AdminController
{
    #[Get('/admin/dashboard', 'admin.dashboard')]
    #[Middleware(['auth', 'admin'])]
    public function dashboard(Request $request): Response
    {
        return Response::view('admin.dashboard');
    }

    #[Post('/admin/users', 'admin.users.store')]
    #[Middleware(['auth', 'admin', 'csrf'])]
    public function createUser(Request $request): Response
    {
        // Create user logic
    }
}
```

### Class-Level Middleware

```php
<?php

namespace App\Controllers;

use Nexus\Http\Route\Middleware;

#[Middleware(['auth'])]
class DashboardController
{
    // All methods inherit 'auth' middleware

    #[Get('/dashboard', 'dashboard')]
    public function index(Request $request): Response
    {
        return Response::view('dashboard');
    }

    #[Get('/dashboard/settings', 'dashboard.settings')]
    public function settings(Request $request): Response
    {
        return Response::view('dashboard.settings');
    }
}
```

## Route Parameters

Access route parameters in controller methods:

```php
class UserController
{
    #[Get('/users/{id}', 'users.show')]
    public function show(Request $request, int $id): Response
    {
        // Method 1: Parameter injection (recommended)
        $user = User::find($id);

        return Response::json($user);
    }

    #[Get('/users/{userId}/posts/{postId}', 'users.posts.show')]
    public function showPost(Request $request, int $userId, int $postId): Response
    {
        // Access multiple parameters
        $user = User::find($userId);
        $post = $user->posts()->find($postId);

        return Response::json($post);
    }
}
```

## Request Data

Access request data in controllers:

```php
class PostController
{
    #[Post('/posts', 'posts.store')]
    public function store(Request $request): Response
    {
        // Get all input
        $data = $request->all();

        // Get specific input
        $title = $request->input('title');
        $content = $request->input('content');

        // Get with default value
        $status = $request->input('status', 'draft');

        // Magic property access
        $title = $request->title;

        // Get query parameters
        $page = $request->query('page', 1);

        // Get uploaded files
        $files = $request->files();
        $avatar = $request->file('avatar');

        // Validate input
        $validated = validate($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        $post = Post::create($validated);
        return Response::json($post, 201);
    }
}
```

## API Controllers

Example of a complete API controller:

```php
<?php

namespace App\Controllers\Api;

use App\Models\User;
use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Route\{Get, Post, Put, Delete, Middleware};

#[Middleware(['api', 'auth:api'])]
class UserApiController
{
    /**
     * List all users with pagination
     */
    #[Get('/api/users', 'api.users.index')]
    public function index(Request $request): Response
    {
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);

        $users = User::paginate($page, $perPage);

        return Response::json([
            'data' => $users,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage
            ]
        ]);
    }

    /**
     * Create a new user
     */
    #[Post('/api/users', 'api.users.store')]
    public function store(Request $request): Response
    {
        $validated = validate($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $validated['password'] = password_hash($validated['password'], PASSWORD_BCRYPT);
        $user = User::create($validated);

        return Response::json([
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Show a specific user
     */
    #[Get('/api/users/{id}', 'api.users.show')]
    public function show(Request $request, int $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json([
                'error' => 'User not found'
            ], 404);
        }

        return Response::json(['data' => $user]);
    }

    /**
     * Update a user
     */
    #[Put('/api/users/{id}', 'api.users.update')]
    public function update(Request $request, int $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }

        $validated = validate($request->all(), [
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $id
        ]);

        $user->update($validated);

        return Response::json([
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Delete a user
     */
    #[Delete('/api/users/{id}', 'api.users.destroy')]
    public function destroy(Request $request, int $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }

        $user->delete();

        return Response::json([
            'message' => 'User deleted successfully'
        ]);
    }
}
```

## Best Practices

1. **Single Responsibility**: Each controller should handle a specific resource or feature
2. **Type Hints**: Always type-hint parameters and return types
3. **Validation**: Validate all input data
4. **DRY Principle**: Extract common logic into services or traits
5. **Consistent Response Format**: Use consistent JSON structure for APIs
6. **Error Handling**: Handle errors gracefully and return appropriate status codes
7. **Documentation**: Add PHPDoc comments for complex methods
8. **Resource Controllers**: Use for standard CRUD operations
9. **Dependency Injection**: Inject dependencies rather than creating them
10. **Keep It Slim**: Move business logic to service classes

## Next Steps

- Learn about [Routing](routing.md)
- Understand [Middleware](middleware.md)
- Explore [Request & Response](request-response.md)
- Work with [Models](models.md)
- Use [Validation](validation.md)
