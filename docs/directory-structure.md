# Directory Structure

Understanding the Nexus Framework directory structure will help you quickly navigate and organize your application.

## Root Directory

```
nexus/
├── app/                    # Application code
├── bootstrap/              # Application bootstrap files
├── config/                 # Configuration files
├── docs/                   # Documentation
├── packages/               # Custom packages
├── public/                 # Web server document root
├── src/                    # Framework core
├── storage/                # Application storage
├── .env                    # Environment configuration
├── .env.example            # Example environment file
├── composer.json           # PHP dependencies
├── docker-compose.yml      # Docker configuration
├── Dockerfile              # Docker image definition
└── nexus                   # CLI entry point
```

## App Directory

Contains your application-specific code:

```
app/
├── Commands/              # Custom CLI commands
├── Controllers/           # HTTP controllers
├── Middleware/            # Custom middleware
├── Models/                # Database models
├── Providers/             # Service providers
├── Validations/           # Validation classes
└── Views/                 # Blade templates
    ├── components/        # Reusable components
    ├── errors/            # Error pages
    ├── layouts/           # Layout templates
    └── partials/          # Partial views
```

### Controllers

HTTP controllers that handle requests:

```
app/Controllers/
├── HomeController.php
├── UserController.php
└── Api/
    └── ApiController.php
```

### Models

Database models:

```
app/Models/
├── User.php
├── Post.php
└── Comment.php
```

### Views

Blade template files:

```
app/Views/
├── welcome.blade.php
├── layouts/
│   └── app.blade.php
├── partials/
│   ├── header.blade.php
│   ├── footer.blade.php
│   └── navigation.blade.php
└── components/
    ├── alert.blade.php
    └── card.blade.php
```

## Bootstrap Directory

Application bootstrap files:

```
bootstrap/
├── app.php               # Application initialization
└── routes.php            # Manual route definitions (optional)
```

### app.php

Creates and configures the application instance.

### routes.php

Optional file for defining routes manually (when not using attributes).

## Config Directory

Configuration files:

```
config/
├── app.php               # Application settings
├── database.php          # Database configuration
└── filesystems.php       # Storage configuration
```

Each file returns an array of configuration options.

## Packages Directory

Custom reusable packages:

```
packages/
└── YourPackage/
    ├── Package.php       # Package registration
    ├── Controllers/      # Package controllers
    ├── Models/           # Package models
    └── Views/            # Package views
```

## Public Directory

Web server document root (publicly accessible):

```
public/
├── index.php             # Application entry point
├── .htaccess             # Apache configuration
├── storage/              # Symlink to storage/app/public
├── uploads/              # Direct file uploads
├── css/                  # Stylesheets
├── js/                   # JavaScript files
└── images/               # Image assets
```

### index.php

The entry point for all HTTP requests. Loads the framework and dispatches requests.

## Src Directory

Framework core files (don't modify):

```
src/
├── Console/              # CLI system
│   ├── Commands/         # Built-in commands
│   ├── stubs/            # Code generation templates
│   ├── Command.php       # Base command class
│   └── Kernel.php        # Command dispatcher
├── Core/                 # Core framework
│   ├── Application.php   # Application container
│   ├── Container.php     # DI container
│   ├── Config.php        # Configuration loader
│   ├── Package.php       # Package base class
│   └── ServiceProvider.php # Provider base class
├── Database/             # Database layer
│   ├── Database.php      # Database connection
│   ├── QueryBuilder.php  # Query builder
│   └── Model.php         # Model base class
├── Http/                 # HTTP layer
│   ├── Request.php       # HTTP request
│   ├── Response.php      # HTTP response
│   ├── Router.php        # Route dispatcher
│   └── Middleware/       # Core middleware
├── Storage/              # File storage
│   └── Storage.php       # Storage manager
├── Validation/           # Validation system
│   ├── Validator.php     # Validator class
│   └── ValidationException.php
├── View/                 # View system
│   ├── View.php          # View renderer
│   └── ViewCompiler.php  # Blade compiler
└── helpers.php           # Global helper functions
```

## Storage Directory

Application storage (not publicly accessible):

```
storage/
├── app/                  # Application files
│   ├── public/           # Publicly accessible files
│   └── private/          # Private files
├── framework/            # Framework files
│   ├── down              # Maintenance mode file
│   └── views/            # Compiled views
├── logs/                 # Log files
│   └── app.log
└── cache/                # Cache files
```

### storage/app

Store application files:
- **public/** - Files accessible via `/storage` URL
- **private/** - Files not publicly accessible

### storage/framework

Framework-generated files:
- **views/** - Compiled Blade templates
- **down** - Maintenance mode indicator

### storage/logs

Application log files.

### storage/cache

Cached data and temporary files.

## Docker Files

Docker configuration:

```
docker/
├── nginx.conf            # Nginx configuration
└── supervisord.conf      # Supervisor configuration
```

## Configuration Files

### .env

Environment-specific configuration:

```env
APP_NAME="Nexus Framework"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=root
DB_PASSWORD=secret
```

### composer.json

PHP dependencies and autoloading:

```json
{
  "name": "nexus/nexus",
  "description": "Nexus Framework",
  "require": {
    "php": "^8.1"
  },
  "autoload": {
    "psr-4": {
      "Nexus\\": "src/",
      "App\\": "app/"
    },
    "files": [
      "src/helpers.php"
    ]
  }
}
```

## File Naming Conventions

### Controllers

- **PascalCase**: `UserController.php`
- **Suffix**: Always end with `Controller`

### Models

- **PascalCase**: `User.php`
- **Singular**: Model names are singular

### Views

- **kebab-case**: `user-profile.blade.php`
- **Extension**: Always `.blade.php` for Blade templates

### Middleware

- **PascalCase**: `AuthMiddleware.php`
- **Suffix**: End with `Middleware`

### Commands

- **PascalCase**: `SendEmailsCommand.php`
- **Suffix**: End with `Command`

## Namespace Structure

```
Nexus\                    # Framework namespace
├── Console\
├── Core\
├── Database\
├── Http\
├── Storage\
├── Validation\
└── View\

App\                      # Application namespace
├── Commands\
├── Controllers\
├── Middleware\
├── Models\
├── Providers\
└── Validations\
```

## Autoloading

PSR-4 autoloading is configured in `composer.json`:

```json
"autoload": {
  "psr-4": {
    "Nexus\\": "src/",
    "App\\": "app/"
  }
}
```

After adding new classes, run:

```bash
composer dump-autoload
```

## Adding New Directories

To add custom directories:

1. Create directory in `app/`
2. Add namespace to `composer.json`
3. Run `composer dump-autoload`

Example for `app/Services/`:

```json
"autoload": {
  "psr-4": {
    "Nexus\\": "src/",
    "App\\": "app/",
    "App\\Services\\": "app/Services/"
  }
}
```

## Best Practices

1. **Organize by Feature**: Group related files together
2. **Consistent Naming**: Follow naming conventions
3. **Separation of Concerns**: Keep business logic in services
4. **Reusable Code**: Extract common code into packages
5. **Documentation**: Document complex directory structures
6. **Version Control**: Don't commit `storage/` or `.env`

## Next Steps

- Learn about [Installation](installation.md)
- Understand [Configuration](configuration.md)
- Explore [Routing](routing.md)
