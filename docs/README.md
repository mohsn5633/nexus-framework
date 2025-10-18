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

### Views & Frontend
- [Blade Templates](blade-templates.md) - Use the Blade templating engine
- [Views](views.md) - Render views and pass data
- [Assets](assets.md) - Manage CSS, JavaScript, and images

### Advanced Features
- [Validation](validation.md) - Validate user input
- [File Storage](file-storage.md) - Store and manage files
- [Service Providers](service-providers.md) - Bootstrap application services
- [Dependency Injection](dependency-injection.md) - Use the IoC container

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
git clone https://github.com/nexus-framework/nexus.git
cd nexus

# Install dependencies
composer install

# Configure environment
cp .env.example .env

# Start development server
php nexus serve
```

Visit `http://localhost:8000` to see your application.

## System Requirements

- PHP 8.1 or higher
- Composer
- PDO PHP Extension (for database)
- Mbstring PHP Extension

## Features

- ⚡ **Lightning Fast** - Optimized for performance
- 🎨 **Blade Templates** - Elegant templating engine
- 🗄️ **Query Builder** - Fluent database interface
- ✅ **Validation** - 20+ built-in validation rules
- 🛣️ **Routing** - Attribute-based and fluent routing
- 🔐 **Middleware** - Request filtering system
- 📦 **Service Providers** - Organize bootstrap logic
- ⚙️ **CLI Commands** - Powerful command-line tools
- 🐳 **Docker Ready** - Containerized deployment
- 💾 **File Storage** - Unified filesystem API

## Community & Support

- **GitHub**: [github.com/nexus-framework/nexus](https://github.com/nexus-framework/nexus)
- **Issues**: [Report bugs and issues](https://github.com/nexus-framework/nexus/issues)
- **Discussions**: [Community discussions](https://github.com/nexus-framework/nexus/discussions)

## License

Nexus Framework is open-source software licensed under the [MIT license](../LICENSE).
