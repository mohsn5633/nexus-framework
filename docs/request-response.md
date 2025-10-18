# Request & Response

Understanding HTTP requests and responses is essential for building web applications. Nexus Framework provides intuitive Request and Response objects.

## Table of Contents

- [Request Object](#request-object)
- [Response Object](#response-object)
- [Request Methods](#request-methods)
- [Response Types](#response-types)
- [Headers](#headers)
- [Cookies](#cookies)
- [File Uploads](#file-uploads)

## Request Object

The Request object provides access to all incoming HTTP request data.

### Accessing the Request

```php
use Nexus\Http\Request;
use Nexus\Http\Response;

#[Get('/users', 'users.index')]
public function index(Request $request): Response
{
    // Request is automatically injected
}
```

## Request Methods

### Getting Input Data

```php
// Get all input (POST + GET)
$data = $request->all();

// Get specific input
$name = $request->input('name');

// Get with default value
$status = $request->input('status', 'active');

// Magic property access
$email = $request->email;

// Check if input exists
if ($request->has('email')) {
    $email = $request->email;
}

// Check if input is present and not empty
if ($request->filled('email')) {
    // Email is present and not empty
}
```

### Query Parameters

```php
// Get query parameter
$page = $request->query('page');

// Get with default
$perPage = $request->query('per_page', 15);

// Get all query parameters
$query = $request->query();
```

### Request Path & URL

```php
// Get path
$path = $request->path();
// Returns: /users/123

// Get full URL
$url = $request->url();
// Returns: http://example.com/users/123

// Get method
$method = $request->method();
// Returns: GET, POST, PUT, DELETE, etc.

// Check method
if ($request->isMethod('POST')) {
    // Handle POST request
}
```

### Request Headers

```php
// Get header
$userAgent = $request->header('User-Agent');

// Get with default
$accept = $request->header('Accept', 'text/html');

// Get all headers
$headers = $request->headers();

// Check header existence
if ($request->hasHeader('Authorization')) {
    $token = $request->header('Authorization');
}
```

### Client Information

```php
// Get client IP address
$ip = $request->ip();

// Get user agent
$userAgent = $request->userAgent();

// Check if request is AJAX
if ($request->ajax()) {
    // Handle AJAX request
}

// Check if request expects JSON
if ($request->expectsJson()) {
    return Response::json($data);
}
```

### Route Parameters

```php
#[Get('/users/{id}/posts/{postId}', 'users.posts.show')]
public function show(Request $request, int $id, int $postId): Response
{
    // Method 1: Via method parameters (recommended)
    $userId = $id;
    $postId = $postId;

    // Method 2: Via request object
    $userId = $request->route('id');
    $postId = $request->route('postId');

    return Response::json([
        'user_id' => $userId,
        'post_id' => $postId
    ]);
}
```

### JSON Requests

```php
// Get JSON payload
$data = $request->all(); // Automatically parses JSON

// Check if request is JSON
if ($request->isJson()) {
    $data = $request->all();
}

// Get specific JSON field
$name = $request->input('user.name');
$email = $request->input('user.email');
```

## File Uploads

### Accessing Files

```php
// Get all uploaded files
$files = $request->files();

// Get specific file
$avatar = $request->file('avatar');

// Check if file was uploaded
if ($request->hasFile('avatar')) {
    $file = $request->file('avatar');
}
```

### File Information

```php
$file = $request->file('avatar');

// File properties (from $_FILES)
$name = $file['name'];          // Original filename
$size = $file['size'];          // Size in bytes
$type = $file['type'];          // MIME type
$tmpName = $file['tmp_name'];   // Temporary path
$error = $file['error'];        // Upload error code

// Check upload success
if ($file['error'] === UPLOAD_ERR_OK) {
    // File uploaded successfully
}
```

### Validating Files

```php
$file = $request->file('avatar');

// Validate file size (5MB max)
if ($file['size'] > 5 * 1024 * 1024) {
    return Response::json(['error' => 'File too large'], 400);
}

// Validate MIME type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowedTypes)) {
    return Response::json(['error' => 'Invalid file type'], 400);
}

// Validate extension
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array(strtolower($extension), $allowedExtensions)) {
    return Response::json(['error' => 'Invalid extension'], 400);
}
```

## Response Object

The Response object represents the HTTP response sent back to the client.

### Creating Responses

```php
use Nexus\Http\Response;

// Plain text response
return Response::text('Hello World');

// JSON response
return Response::json(['message' => 'Success']);

// View response
return Response::view('welcome');

// Redirect response
return Response::redirect('/dashboard');
```

## Response Types

### JSON Response

```php
// Simple JSON
return Response::json(['name' => 'John']);

// With status code
return Response::json(['error' => 'Not found'], 404);

// Pretty print
return Response::json($data, 200);
```

### View Response

```php
// Render view
return Response::view('dashboard');

// With data
return Response::view('users.profile', [
    'user' => $user,
    'posts' => $posts
]);

// With status code
return Response::view('errors.404', [], 404);
```

### Text Response

```php
// Plain text
return Response::text('Hello World');

// With status code
return Response::text('Forbidden', 403);
```

### HTML Response

```php
$html = '<h1>Hello World</h1>';
return new Response($html, 200, ['Content-Type' => 'text/html']);
```

### Redirect Response

```php
// Simple redirect
return Response::redirect('/login');

// Redirect with status code
return Response::redirect('/dashboard', 302);

// Redirect back
return Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
```

### Download Response

```php
// Force file download
$file = storage_path('app/documents/invoice.pdf');
$content = file_get_contents($file);

return new Response($content, 200, [
    'Content-Type' => 'application/pdf',
    'Content-Disposition' => 'attachment; filename="invoice.pdf"'
]);
```

### Stream Response

```php
// Stream large file
$file = storage_path('app/videos/large-video.mp4');

return new Response('', 200, [
    'Content-Type' => 'video/mp4',
    'Content-Length' => filesize($file),
    'X-Accel-Redirect' => $file  // For Nginx
]);
```

## Response Headers

### Setting Headers

```php
$response = Response::json(['data' => $data]);

// Set single header
$response->headers['Content-Type'] = 'application/json';

// Set multiple headers
$response->headers = array_merge($response->headers, [
    'X-Custom-Header' => 'Value',
    'Cache-Control' => 'no-cache, must-revalidate'
]);

return $response;
```

### Common Headers

```php
// CORS headers
$response->headers['Access-Control-Allow-Origin'] = '*';
$response->headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE';

// Cache control
$response->headers['Cache-Control'] = 'public, max-age=3600';

// Content type
$response->headers['Content-Type'] = 'application/json';

// Custom headers
$response->headers['X-API-Version'] = '1.0';
```

## HTTP Status Codes

### Success Codes

```php
// 200 OK
return Response::json($data, 200);

// 201 Created
return Response::json($user, 201);

// 204 No Content
return new Response('', 204);
```

### Redirection Codes

```php
// 301 Moved Permanently
return Response::redirect('/new-url', 301);

// 302 Found
return Response::redirect('/temp-url', 302);
```

### Client Error Codes

```php
// 400 Bad Request
return Response::json(['error' => 'Invalid input'], 400);

// 401 Unauthorized
return Response::json(['error' => 'Unauthorized'], 401);

// 403 Forbidden
return Response::json(['error' => 'Forbidden'], 403);

// 404 Not Found
return Response::json(['error' => 'Not found'], 404);

// 422 Unprocessable Entity
return Response::json(['errors' => $validationErrors], 422);

// 429 Too Many Requests
return Response::json(['error' => 'Rate limit exceeded'], 429);
```

### Server Error Codes

```php
// 500 Internal Server Error
return Response::json(['error' => 'Server error'], 500);

// 503 Service Unavailable
return Response::json(['error' => 'Maintenance mode'], 503);
```

## Cookies

### Setting Cookies

```php
// Set cookie
setcookie('user_preference', 'dark_mode', [
    'expires' => time() + (86400 * 30), // 30 days
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

return Response::json(['success' => true]);
```

### Reading Cookies

```php
// Get cookie
$preference = $_COOKIE['user_preference'] ?? 'light_mode';

// Check if cookie exists
if (isset($_COOKIE['session_id'])) {
    $sessionId = $_COOKIE['session_id'];
}
```

### Deleting Cookies

```php
// Delete cookie by setting expiration to past
setcookie('user_preference', '', time() - 3600, '/');
```

## Complete Examples

### API Endpoint

```php
<?php

namespace App\Controllers\Api;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Route\{Get, Post, Put, Delete};

class UserApiController
{
    #[Get('/api/users', 'api.users.index')]
    public function index(Request $request): Response
    {
        // Get query parameters
        $page = $request->query('page', 1);
        $search = $request->query('search');

        // Build query
        $query = User::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $users = $query->paginate($page, 15);

        return Response::json([
            'data' => $users,
            'meta' => [
                'page' => $page,
                'per_page' => 15
            ]
        ]);
    }

    #[Post('/api/users', 'api.users.store')]
    public function store(Request $request): Response
    {
        // Validate input
        try {
            $validated = validate($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8'
            ]);
        } catch (ValidationException $e) {
            return Response::json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Hash password
        $validated['password'] = password_hash($validated['password'], PASSWORD_BCRYPT);

        // Create user
        $user = User::create($validated);

        return Response::json([
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    #[Put('/api/users/{id}', 'api.users.update')]
    public function update(Request $request, int $id): Response
    {
        $user = User::find($id);

        if (!$user) {
            return Response::json([
                'error' => 'User not found'
            ], 404);
        }

        // Validate input
        try {
            $validated = validate($request->all(), [
                'name' => 'string|max:255',
                'email' => "email|unique:users,email,{$id}"
            ]);
        } catch (ValidationException $e) {
            return Response::json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        $user->update($validated);

        return Response::json([
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    #[Delete('/api/users/{id}', 'api.users.destroy')]
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

### File Upload Endpoint

```php
#[Post('/upload', 'upload')]
public function upload(Request $request): Response
{
    // Check if file exists
    if (!$request->hasFile('file')) {
        return Response::json([
            'error' => 'No file uploaded'
        ], 400);
    }

    $file = $request->file('file');

    // Validate upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return Response::json([
            'error' => 'Upload failed'
        ], 400);
    }

    // Validate size (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return Response::json([
            'error' => 'File size exceeds 5MB limit'
        ], 400);
    }

    // Validate type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return Response::json([
            'error' => 'Invalid file type. Only JPG, PNG, and GIF allowed'
        ], 400);
    }

    // Store file
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;

    $path = Storage::disk('public')->putFileAs('uploads', $file, $filename);

    if (!$path) {
        return Response::json([
            'error' => 'Failed to save file'
        ], 500);
    }

    // Get public URL
    $url = Storage::disk('public')->url($path);

    return Response::json([
        'success' => true,
        'path' => $path,
        'url' => $url,
        'filename' => $filename
    ], 201);
}
```

## Best Practices

1. **Validate Input**: Always validate user input
2. **Type Hints**: Use proper type hints
3. **Status Codes**: Use appropriate HTTP status codes
4. **Error Messages**: Provide clear error messages
5. **Security**: Sanitize and escape data
6. **Headers**: Set proper response headers
7. **File Uploads**: Validate file types and sizes
8. **JSON Responses**: Use consistent response format

## Next Steps

- Learn about [Routing](routing.md)
- Understand [Controllers](controllers.md)
- Explore [Middleware](middleware.md)
- Work with [Validation](validation.md)
