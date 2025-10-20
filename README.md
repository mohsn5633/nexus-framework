# Nexus Framework

A smart, lightweight PHP framework that's easier to use than Laravel while maintaining professional-grade features.

## Features

- **Modern PHP 8.1+** - Built for the latest PHP features
- **Smart Routing** - PHP 8 attributes + fluent API for route definitions
- **Automatic DI** - Powerful dependency injection container with auto-resolution
- **Query Builder** - Elegant, intuitive database query builder with PDO
- **Simple Models** - Active Record pattern for database interactions
- **HTTP Client** - Powerful CURL-based HTTP client with retry, middleware, and async support
- **Socket Server** - TCP/UDP/SSL/TLS socket server and WebSocket (RFC 6455) implementation
- **Process Management** - Multi-process and worker pool for parallel task execution
- **Queue System** - Background job processing with multiple queue drivers
- **Task Scheduler** - Cron-like task scheduling system
- **Configuration** - Dot notation config with environment variable support
- **Middleware** - Clean middleware pipeline for request/response handling
- **Packages** - Extensible plugin system for modular development
- **Docker Ready** - Complete Docker setup with MySQL and phpMyAdmin
- **Composer Support** - PSR-4 autoloading and package management
- **CLI Tool** - Artisan-like command interface for code generation
- **Testing** - PHPUnit integration with 79 comprehensive tests

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

// HTTP Client
$client = http();
$response = $client->get('https://api.example.com/users');

// Sockets
$socket = socket('tcp');
$socket->connect('example.com', 80);

// Process
$process = process('php script.php');
$process->run();

// Worker Pool
$pool = worker_pool(4);
$pool->add(fn() => heavyTask());
```

## HTTP Client

Make HTTP requests with retry logic, middleware, and async support:

```php
use Nexus\Http\Client\HttpClient;

$client = http();

// Simple GET request
$response = $client->get('https://api.example.com/users');
$data = $response->json();

// POST with data
$response = $client->post('https://api.example.com/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// With headers and authentication
$client->withHeaders(['X-API-Key' => 'secret'])
       ->withBasicAuth('username', 'password')
       ->get('https://api.example.com/protected');

// Async requests
$client->async()
       ->get('https://api.example.com/endpoint1')
       ->then(function($response) {
           echo "Response: " . $response->body();
       });

// Retry on failure
$client->retry(3, 1000) // 3 retries, 1 second delay
       ->get('https://unreliable-api.com/data');
```

See [docs/http-client.md](docs/http-client.md) for complete documentation.

## Socket Server

Build real-time applications with TCP/UDP sockets and WebSocket:

```php
use Nexus\Socket\SocketServer;
use Nexus\Socket\WebSocket;

// TCP Socket Server
$server = SocketServer::tcp('0.0.0.0', 8080);
$server->on('connect', function($client) {
    echo "Client connected!\n";
});

$server->on('data', function($client, $data) {
    echo "Received: {$data}\n";
    $server->send($client, "Echo: {$data}");
});

$server->start();

// WebSocket Server (RFC 6455)
$ws = new WebSocket('0.0.0.0', 8080);

$ws->on('connect', function($client) {
    echo "WebSocket client connected\n";
});

$ws->on('message', function($client, $message) use ($ws) {
    // Broadcast to all clients
    $ws->broadcast("User said: {$message}");
});

$ws->start();
```

See [docs/sockets.md](docs/sockets.md) for complete documentation.

## Process Management

Execute parallel tasks with worker pools:

```php
use Nexus\Process\Process;
use Nexus\Process\ProcessPool;

// Single process
$process = new Process('php artisan queue:work');
$process->setTimeout(3600);
$process->run();

echo $process->getOutput();

// Worker pool for parallel execution
$pool = new ProcessPool(4); // 4 workers

$tasks = [];
for ($i = 1; $i <= 100; $i++) {
    $tasks[] = function() use ($i) {
        return processData($i);
    };
}

$pool->map($tasks, function($result) {
    echo "Task completed: {$result}\n";
});

// Wait for all tasks
$pool->wait();
```

See [docs/process.md](docs/process.md) for complete documentation.

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
- PDO extension (for database features)
- OpenSSL extension (for encryption and SSL/TLS sockets)
- CURL extension (for HTTP client)
- Mbstring extension
- MySQL/PostgreSQL/SQLite (for database features)

## Documentation

For comprehensive documentation, examples, and guides, visit the [documentation folder](docs/README.md):

- [HTTP Client](docs/http-client.md) - Make HTTP requests with retry and async support
- [Socket Server](docs/sockets.md) - Build real-time applications with TCP/UDP/WebSocket
- [Process Management](docs/process.md) - Execute parallel tasks with worker pools
- [Queue System](docs/queues.md) - Background job processing
- [Task Scheduler](docs/scheduler.md) - Cron-like task scheduling
- [Database](docs/database.md) - Database connections and queries
- [Routing](docs/routing.md) - Define application routes
- [Validation](docs/validation.md) - Validate user input
- And much more...

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
