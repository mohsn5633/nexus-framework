# Nexus Framework Documentation

Welcome to the official documentation for **Nexus Framework** - a modern, lightweight PHP framework built with PHP 8.1+ features.

## Table of Contents

### Getting Started
- [Installation](installation.md) - Install and configure Nexus Framework
- [Configuration](configuration.md) - Configure your application settings
- [Directory Structure](directory-structure.md) - Understand the framework structure

### Core Concepts
- [Routing](routing.md) - Define routes using attributes or fluent API
- [Controllers](controllers.md) - Create and organize controllers
- [Middleware](middleware.md) - Filter HTTP requests
- [Request & Response](request-response.md) - Handle HTTP requests and responses

### Database
- [Database Basics](database.md) - Connect to databases and execute queries
- [Query Builder](query-builder.md) - Build database queries fluently
- [Models](models.md) - Work with Eloquent-style models
- [Migrations](migrations.md) - Database schema versioning
- [Seeders](seeders.md) - Populate database with test data

### Views & Frontend
- [Blade Templates](blade-templates.md) - Use the Blade templating engine
- [Views](views.md) - Render views and pass data
- [Assets](assets.md) - Manage CSS, JavaScript, and images

### Networking & Async
- [HTTP Client](http-client.md) - Make HTTP/HTTPS requests with CURL
- [Socket Server](sockets.md) - TCP/UDP/SSL/TLS sockets and WebSocket server
- [Process Management](process.md) - Multi-process and worker pools for parallel execution

### Background Processing
- [Queues](queues.md) - Background job processing
- [Task Scheduler](scheduler.md) - Cron-like task scheduling
- [Mail](mail.md) - Send emails using SMTP or other drivers

### Security & Data
- [Validation](validation.md) - Validate user input
- [Encryption](encryption.md) - Encrypt and decrypt data
- [Session](session.md) - Manage user sessions
- [Cache](cache.md) - Cache data for performance
- [Rate Limiting](rate-limiting.md) - Protect against abuse

### Advanced Features
- [File Storage](file-storage.md) - Store and manage files
- [Service Providers](service-providers.md) - Bootstrap application services
- [Dependency Injection](dependency-injection.md) - Use the IoC container
- [Pagination](pagination.md) - Paginate database results
- [Dates](dates.md) - Work with dates and times
- [Protocol](protocol.md) - HTTP/HTTPS protocol handling

### CLI & Development
- [Artisan Commands](artisan-commands.md) - Use the CLI tool
- [Creating Commands](creating-commands.md) - Build custom CLI commands
- [Package Development](package-development.md) - Create reusable packages

### Deployment
- [Deployment](deployment.md) - Deploy your application
- [Docker](docker.md) - Use Docker for development and production

## Quick Start

```bash
# Clone the repository
git clone https://github.com/mohsn5633/nexus-framework.git
cd nexus-framework

# Install dependencies
composer install

# Configure environment
cp .env.example .env

# Start development server
php nexus serve
```

Visit `http://localhost:8000` to see your application.

## Quick Examples

### HTTP Client
```php
// Make API requests
$client = http();
$response = $client->get('https://api.github.com/users/octocat');
$data = $response->json();
```

### WebSocket Server
```php
// Create a real-time chat server
$ws = websocket('0.0.0.0', 8080);
$ws->on('message', function($client, $msg) use ($ws) {
    $ws->broadcast($msg); // Send to all clients
});
$ws->start();
```

### Parallel Processing
```php
// Process 100 tasks in parallel with 4 workers
$pool = worker_pool(4);
$results = $pool->map($tasks, function($result) {
    echo "Completed: {$result}\n";
});
```

## System Requirements

- PHP 8.1 or higher
- Composer
- PDO PHP Extension (for database)
- Mbstring PHP Extension
- OpenSSL Extension (for encryption and SSL/TLS sockets)
- CURL Extension (for HTTP client)
- PCNTL Extension (optional, for signal handling on Unix/Linux)

## Features

- ⚡ **Lightning Fast** - Optimized for performance
- 🎨 **Blade Templates** - Elegant templating engine
- 🗄️ **Query Builder** - Fluent database interface
- ✅ **Validation** - 20+ built-in validation rules
- 🛣️ **Routing** - Attribute-based and fluent routing
- 🔐 **Middleware** - Request filtering system
- 🌐 **HTTP Client** - CURL-based HTTP client with retry and async
- 🔌 **Socket Server** - TCP/UDP/WebSocket (RFC 6455) support
- 🔄 **Process Pool** - Parallel task execution with workers
- 📬 **Queue System** - Background job processing
- ⏰ **Task Scheduler** - Cron-like scheduling
- 📦 **Service Providers** - Organize bootstrap logic
- ⚙️ **CLI Commands** - Powerful command-line tools
- 🐳 **Docker Ready** - Containerized deployment
- 💾 **File Storage** - Unified filesystem API
- 🧪 **Testing** - PHPUnit with 79+ tests included

## Community & Support

- **GitHub**: [github.com/mohsn5633/nexus-framework](https://github.com/mohsn5633/nexus-framework)
- **Issues**: [Report bugs and issues](https://github.com/mohsn5633/nexus-framework/issues)
- **Discussions**: [Community discussions](https://github.com/mohsn5633/nexus-framework/discussions)

## License

Nexus Framework is open-source software licensed under the [MIT license](../LICENSE).
