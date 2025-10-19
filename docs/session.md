# Session Management

Nexus Framework provides a robust session management system with support for multiple storage drivers, flash data, and CSRF protection.

## Table of Contents

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [Session Drivers](#session-drivers)
- [Flash Data](#flash-data)
- [CSRF Protection](#csrf-protection)
- [Session Security](#session-security)

## Introduction

The Session system provides a simple, consistent API for storing user data across requests. Sessions are automatically started when using the `StartSessionMiddleware`.

### Features

- **Multiple Drivers**: File, Database, and Array drivers
- **Flash Data**: Store data for the next request only
- **CSRF Protection**: Built-in CSRF token generation
- **Secure**: HttpOnly cookies, SameSite protection
- **Easy to Use**: Simple, intuitive API

## Configuration

Session configuration is stored in `config/session.php`.

### Environment Variables

```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_COOKIE=nexus_session
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

### Configuration Options

```php
return [
    'driver' => 'file',           // file, database, array
    'lifetime' => 120,             // Minutes
    'encrypt' => false,            // Encrypt session data
    'files' => storage_path('framework/sessions'),
    'connection' => 'mysql',       // Database connection
    'table' => 'sessions',         // Database table
    'cookie' => 'nexus_session',   // Cookie name
    'path' => '/',
    'domain' => null,
    'secure' => false,             // HTTPS only
    'http_only' => true,           // HTTP access only
    'same_site' => 'lax',          // lax, strict, none
];
```

## Basic Usage

### Storing Data

```php
// Using helper function
session(['key' => 'value']);
session()->put('name', 'John Doe');

// Using SessionManager
use Nexus\Session\SessionManager;

public function store(SessionManager $session)
{
    $session->put('user_id', 123);
    $session->put('role', 'admin');
}
```

### Retrieving Data

```php
// Get value with default
$name = session('name', 'Guest');
$userId = session()->get('user_id', 0);

// Get all session data
$all = session()->all();
```

### Checking Existence

```php
if (session()->has('user_id')) {
    // User is logged in
}
```

### Removing Data

```php
// Remove a single item
session()->forget('name');

// Remove multiple items
session()->forget(['user_id', 'role']);

// Remove all session data
session()->flush();
```

## Session Drivers

### File Driver (Default)

Stores sessions in files on the server.

```php
'driver' => 'file',
'files' => storage_path('framework/sessions'),
```

**Pros**: Simple, no external dependencies
**Cons**: Can be slow with many files

### Database Driver

Stores sessions in a database table.

#### Migration

Create sessions table:

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX(last_activity)
);
```

#### Configuration

```php
'driver' => 'database',
'connection' => 'mysql',
'table' => 'sessions',
```

**Pros**: Scalable, easy to query
**Cons**: Requires database

### Array Driver

Stores sessions in memory (testing only).

```php
'driver' => 'array',
```

**Pros**: Fast, perfect for testing
**Cons**: Data lost after request

## Flash Data

Flash data is session data that only lasts for the next request.

### Storing Flash Data

```php
// Flash a single value
session()->flash('success', 'User created successfully!');

// In controller
public function store(Request $request, SessionManager $session)
{
    // Save user...

    $session->flash('success', 'User created!');

    return redirect('/users');
}
```

### Retrieving Flash Data

```php
// In view
@if(session()->has('success'))
    <div class="alert alert-success">
        {{ session()->get('success') }}
    </div>
@endif
```

### Reflashing

Keep flash data for another request:

```php
// Reflash all flash data
session()->reflash();

// Keep specific flash data
session()->keep(['success', 'error']);
```

## CSRF Protection

### Getting CSRF Token

```php
// In controller
$token = session()->token();

// Or using helper
$token = csrf_token();
```

### Using in Forms

```blade
<form method="POST" action="/users">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <!-- Or use helper -->
    {{ csrf_token() }}

    <!-- Form fields -->
</form>
```

### Verifying CSRF Token

```php
use Nexus\Http\Request;
use Nexus\Session\SessionManager;

public function store(Request $request, SessionManager $session)
{
    $token = $request->input('_token');

    if ($token !== $session->token()) {
        throw new \Exception('CSRF token mismatch');
    }

    // Process request...
}
```

### Regenerating Token

```php
// Regenerate CSRF token
session()->regenerateToken();
```

## Session Security

### Regenerating Session ID

Regenerate session ID to prevent fixation attacks:

```php
// After login
public function login(Request $request, SessionManager $session)
{
    // Authenticate user...

    // Regenerate session ID
    $session->regenerate();

    // Store user data
    $session->put('user_id', $user->id);
}
```

### Destroying Sessions

```php
// Destroy session (logout)
public function logout(SessionManager $session)
{
    $session->destroy();

    return redirect('/login');
}
```

### Session Lifetime

Sessions expire after the configured lifetime:

```php
// config/session.php
'lifetime' => 120, // 120 minutes
```

### Secure Cookies

For production, enable secure cookies:

```env
SESSION_SECURE_COOKIE=true  # HTTPS only
SESSION_HTTP_ONLY=true       # No JavaScript access
SESSION_SAME_SITE=strict     # CSRF protection
```

## Complete Examples

### User Authentication

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Session\SessionManager;
use App\Models\User;

class AuthController
{
    public function login(Request $request, SessionManager $session): Response
    {
        $credentials = validate($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !password_verify($credentials['password'], $user->password)) {
            $session->flash('error', 'Invalid credentials');
            return redirect('/login');
        }

        // Regenerate session ID for security
        $session->regenerate();

        // Store user data
        $session->put('user_id', $user->id);
        $session->put('user_email', $user->email);

        $session->flash('success', 'Welcome back!');

        return redirect('/dashboard');
    }

    public function logout(SessionManager $session): Response
    {
        $session->destroy();

        return redirect('/login');
    }

    public function check(SessionManager $session): bool
    {
        return $session->has('user_id');
    }
}
```

### Shopping Cart

```php
<?php

namespace App\Controllers;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Session\SessionManager;

class CartController
{
    public function add(Request $request, SessionManager $session): Response
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        // Get current cart
        $cart = $session->get('cart', []);

        // Add or update item
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'id' => $productId,
                'quantity' => $quantity
            ];
        }

        // Save cart
        $session->put('cart', $cart);
        $session->flash('success', 'Item added to cart');

        return redirect('/cart');
    }

    public function view(SessionManager $session): Response
    {
        $cart = $session->get('cart', []);

        return Response::view('cart.index', [
            'cart' => $cart
        ]);
    }

    public function clear(SessionManager $session): Response
    {
        $session->forget('cart');
        $session->flash('success', 'Cart cleared');

        return redirect('/cart');
    }
}
```

## Middleware Setup

Enable session middleware in your application:

```php
// In router or bootstrap
$router->middleware('session', \Nexus\Http\Middleware\StartSessionMiddleware::class);

// Apply to routes
$router->group(['middleware' => 'session'], function($router) {
    // All routes here will have sessions
});
```

## Best Practices

1. **Regenerate on Login**: Always regenerate session ID after authentication
2. **Use HTTPS**: Enable secure cookies in production
3. **Limit Lifetime**: Set appropriate session lifetime
4. **Flash for Messages**: Use flash data for one-time messages
5. **Clean Up**: Implement garbage collection for file/database drivers
6. **Validate CSRF**: Always validate CSRF tokens on forms
7. **Secure Cookies**: Use HttpOnly and SameSite flags
8. **Don't Store Sensitive Data**: Avoid storing passwords or credit cards

## Garbage Collection

Sessions are automatically cleaned up based on lifetime.

### File Driver

```php
// Manually trigger GC
$session->getDriver()->gc($maxLifetime);
```

### Database Driver

```sql
-- Clean up old sessions
DELETE FROM sessions
WHERE last_activity < UNIX_TIMESTAMP() - 7200;
```

## Next Steps

- Learn about [Cache](cache.md)
- Understand [Middleware](middleware.md)
- Explore [Security](security.md)
