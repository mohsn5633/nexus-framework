# Nexus Framework

A smart, lightweight PHP framework that's easier to use than Laravel while maintaining professional-grade features.

## Features

- **Modern PHP 8.1+** - Built for the latest PHP features
- **Smart Routing** - PHP 8 attributes + fluent API for route definitions
- **Automatic DI** - Powerful dependency injection container with auto-resolution
- **Query Builder** - Elegant, intuitive database query builder with PDO
- **Simple Models** - Active Record pattern for database interactions
- **Configuration** - Dot notation config with environment variable support
- **Middleware** - Clean middleware pipeline for request/response handling
- **Packages** - Extensible plugin system for modular development
- **Docker Ready** - Complete Docker setup with MySQL and phpMyAdmin
- **Composer Support** - PSR-4 autoloading and package management
- **CLI Tool** - Artisan-like command interface for code generation

## CLI Commands

Nexus includes a powerful CLI tool for rapid development:

```bash
# Generate controllers
php nexus make:controller UserController --resource

# Generate models
php nexus make:model Post --table=posts

# Generate complete modules
php nexus make:module Product

# Create middleware
php nexus make:middleware AuthMiddleware

# Create packages
php nexus make:package PaymentGateway

# List all routes
php nexus routes:list

# Start development server
php nexus serve --port=8080
```

See [CLI.md](CLI.md) for complete command reference.

## Installation

### Quick Start

```bash
# Clone the repository
git clone <your-repo-url> nexus-app
cd nexus-app

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Start the development server
composer serve
```

Visit `http://localhost:8000` to see your application!

### Docker Setup

```bash
# Start with Docker
docker-compose up -d

# Access the application
# App: http://localhost:8000
# phpMyAdmin: http://localhost:8080
```

## Directory Structure

```
nexus-framework/
├── app/                    # Application code
│   ├── Controllers/        # HTTP controllers
│   ├── Models/            # Database models
│   └── Views/             # View templates
├── bootstrap/             # Framework bootstrapping
│   ├── app.php           # Application initialization
│   └── routes.php        # Route definitions
├── config/                # Configuration files
│   ├── app.php
│   └── database.php
├── docker/                # Docker configuration
├── packages/              # Custom packages
├── public/                # Web root
│   └── index.php         # Entry point
├── src/                   # Framework core
│   ├── Core/             # Core classes
│   ├── Database/         # Database layer
│   ├── Http/             # HTTP layer
│   └── Support/          # Helper classes
└── storage/               # Logs and cache
```

## Routing

### Using Attributes (Recommended)

```php
use Nexus\Http\Route;
use Nexus\Http\Request;
use Nexus\Http\Response;

class UserController
{
    #[Route::get('/users', 'users.index')]
    public function index(Request $request): Response
    {
        $users = User::all();
        return Response::json($users);
    }

    #[Route::get('/users/{id}', 'users.show')]
    public function show(Request $request): Response
    {
        $id = $request->route('id');
        $user = User::find($id);
        return Response::json($user);
    }

    #[Route::post('/users', 'users.store')]
    public function store(Request $request): Response
    {
        $user = User::create($request->input());
        return Response::json($user, 201);
    }
}
```

### Using Fluent API

In `bootstrap/routes.php`:

```php
$router = app('router');

$router->get('/', function () {
    return 'Hello World!';
});

$router->post('/api/users', [UserController::class, 'store']);

// Route groups
$router->group(['prefix' => 'api', 'middleware' => [JsonMiddleware::class]], function ($router) {
    $router->get('/users', [UserController::class, 'index']);
    $router->get('/users/{id}', [UserController::class, 'show']);
});
```

## Database

### Query Builder

```php
use Nexus\Database\Database;

$db = app('db');

// Select
$users = $db->table('users')
    ->where('status', 'active')
    ->orderBy('name', 'ASC')
    ->limit(10)
    ->get();

// Insert
$userId = $db->table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Update
$db->table('users')
    ->where('id', 1)
    ->update(['status' => 'inactive']);

// Delete
$db->table('users')->where('id', 1)->delete();

// Joins
$results = $db->table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select(['users.name', 'posts.title'])
    ->get();
```

### Models

```php
namespace App\Models;

use Nexus\Database\Model;

class User extends Model
{
    protected static ?string $table = 'users';
}

// Usage
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();

$users = User::all();

$user = User::create([
    'name' => 'John Smith',
    'email' => 'john@example.com'
]);

$user->delete();

// Query Builder
User::query()
    ->where('status', 'active')
    ->orderBy('created_at', 'DESC')
    ->limit(5)
    ->get();
```

## Dependency Injection

The container automatically resolves dependencies:

```php
class UserService
{
    public function __construct(
        private Database $db,
        private EmailService $email
    ) {}
}

// Automatic resolution
$service = app(UserService::class);

// Manual binding
app()->singleton('cache', function() {
    return new CacheService();
});

$cache = app('cache');
```

## Middleware

### Creating Middleware

```php
namespace App\Middleware;

use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class RateLimitMiddleware implements Middleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        // Check rate limit
        if ($this->isRateLimited($request->ip())) {
            return Response::json(['error' => 'Too many requests'], 429);
        }

        return $next($request);
    }
}
```

### Applying Middleware

```php
// On routes
$router->get('/api/data', [DataController::class, 'index'])
    ->middleware([AuthMiddleware::class, RateLimitMiddleware::class]);

// On groups
$router->group(['middleware' => [AuthMiddleware::class]], function ($router) {
    // Protected routes
});
```

## Configuration

Access configuration using dot notation:

```php
$appName = config('app.name');
$dbHost = config('database.connections.mysql.host');
$default = config('some.key', 'default value');

// Set configuration
app()->config()->set('custom.setting', 'value');
```

## Helper Functions

```php
// Application
$app = app();
$router = app('router');

// Configuration
$value = config('app.name', 'default');

// Environment
$debug = env('APP_DEBUG', false);

// Paths
$base = base_path('app/Controllers');
$storage = storage_path('logs/app.log');
$public = public_path('assets/style.css');

// Responses
json(['message' => 'Success'], 200);
redirect('/dashboard', 302);

// Views
$html = view('welcome', ['title' => 'Home']);

// Debugging
dd($variable); // Dump and die
dump($variable); // Dump
```

## Packages

Create custom packages in the `packages/` directory:

```php
namespace Packages\MyPackage;

use Nexus\Core\Package as BasePackage;
use Nexus\Core\Application;

class Package extends BasePackage
{
    public function register(Application $app): void
    {
        $app->singleton('myservice', function() {
            return new MyService();
        });
    }

    public function boot(Application $app): void
    {
        $router = $app->router();
        $router->get('/my-package', function() {
            return 'Hello from MyPackage!';
        });
    }
}
```

## Development Commands

```bash
# Start development server
composer serve

# Run tests
composer test

# Install dependencies
composer install

# Update dependencies
composer update
```

## Docker Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f app

# Execute commands in container
docker-compose exec app php artisan custom:command
```

## Requirements

- PHP 8.1 or higher
- Composer
- PDO extension
- MySQL/PostgreSQL/SQLite (for database features)

## Why Nexus?

### vs Laravel

- **Simpler** - Less abstraction, easier to understand
- **Lighter** - Smaller codebase, faster to learn
- **Faster** - Less overhead, better performance
- **Modern** - PHP 8 attributes, cleaner syntax
- **Flexible** - Easy to extend and customize

### vs Slim

- **More Features** - Full-stack capabilities
- **Better DX** - Auto-discovery, smart DI
- **Database** - Built-in query builder and models
- **Packages** - Plugin system for modularity

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License - feel free to use this framework for personal or commercial projects.

## Support

For issues and questions, please open an issue on GitHub or consult the documentation.
