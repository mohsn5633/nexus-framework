# Configuration

Nexus Framework uses environment files and configuration files to manage application settings.

## Environment Configuration

### .env File

The `.env` file contains environment-specific settings. Copy the example file:

```bash
cp .env.example .env
```

### Environment Variables

#### Application Settings

```env
# Application name
APP_NAME="Nexus Framework"

# Environment: local, production, staging
APP_ENV=local

# Enable/disable debug mode
APP_DEBUG=true

# Application URL
APP_URL=http://localhost:8000
```

#### Database Settings

```env
# Database connection type: mysql, pgsql, sqlite
DB_CONNECTION=mysql

# Database host
DB_HOST=127.0.0.1

# Database port
DB_PORT=3306

# Database name
DB_DATABASE=nexus

# Database credentials
DB_USERNAME=root
DB_PASSWORD=secret
```

#### File Storage

```env
# Default storage disk: local, public, uploads
FILESYSTEM_DISK=local

# Max upload size in KB
UPLOAD_MAX_SIZE=10240
```

### Accessing Environment Variables

Use the `env()` helper:

```php
$appName = env('APP_NAME');
$debug = env('APP_DEBUG', false);  // With default value
$url = env('APP_URL', 'http://localhost');
```

## Configuration Files

Configuration files are stored in the `config/` directory.

### app.php

Application-wide settings:

```php
<?php

return [
    'name' => env('APP_NAME', 'Nexus Framework'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'locale' => 'en',

    'providers' => [
        App\Providers\RouteServiceProvider::class,
        App\Providers\ViewServiceProvider::class,
    ],
];
```

### database.php

Database configuration:

```php
<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'nexus'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'nexus'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
        ],
    ],
];
```

### filesystems.php

File storage configuration:

```php
<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => env('APP_URL') . '/uploads',
            'visibility' => 'public',
        ],
    ],

    'upload' => [
        'max_size' => env('UPLOAD_MAX_SIZE', 10240),
        'allowed_extensions' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'documents' => ['pdf', 'doc', 'docx', 'txt'],
        ],
    ],
];
```

## Accessing Configuration

Use the `config()` helper:

```php
// Get configuration value
$appName = config('app.name');
$debug = config('app.debug');

// Get with default value
$timezone = config('app.timezone', 'UTC');

// Get nested configuration
$mysqlHost = config('database.connections.mysql.host');

// Get entire configuration array
$database = config('database');
```

## Creating Custom Configuration

### Create Configuration File

Create `config/services.php`:

```php
<?php

return [
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'aws' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_BUCKET'),
    ],
];
```

### Add to .env

```env
STRIPE_KEY=pk_test_abc123
STRIPE_SECRET=sk_test_xyz789

MAILGUN_DOMAIN=mg.example.com
MAILGUN_SECRET=key-abc123

AWS_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE
AWS_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
AWS_DEFAULT_REGION=us-west-2
AWS_BUCKET=my-bucket
```

### Access Configuration

```php
$stripeKey = config('services.stripe.key');
$awsRegion = config('services.aws.region');
```

## Environment-Specific Configuration

### Local Environment

`.env.local`:

```env
APP_ENV=local
APP_DEBUG=true
DB_HOST=localhost
```

### Production Environment

`.env.production`:

```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=production-db.example.com
```

### Staging Environment

`.env.staging`:

```env
APP_ENV=staging
APP_DEBUG=true
DB_HOST=staging-db.example.com
```

## Configuration Caching

For production, cache configuration for better performance:

```bash
# Cache configuration (future feature)
php nexus config:cache

# Clear configuration cache
php nexus config:clear
```

## Environment Detection

Detect the current environment:

```php
$environment = env('APP_ENV', 'production');

if ($environment === 'local') {
    // Development code
}

if ($environment === 'production') {
    // Production code
}

// Or use config
if (config('app.debug')) {
    // Debug mode enabled
}
```

## Best Practices

### Security

1. **Never commit .env**: Add to `.gitignore`
2. **Use environment variables**: For sensitive data
3. **Disable debug in production**: Set `APP_DEBUG=false`
4. **Secure database credentials**: Use strong passwords
5. **HTTPS in production**: Always use HTTPS

### Organization

1. **Group related settings**: Keep configuration organized
2. **Use descriptive names**: Clear configuration keys
3. **Provide defaults**: Always have fallback values
4. **Document custom config**: Add comments for clarity
5. **Environment-specific files**: Use `.env.example` as template

### Performance

1. **Cache configuration**: In production environments
2. **Minimize config files**: Keep them small and focused
3. **Use environment variables**: Instead of hardcoding values
4. **Lazy loading**: Only load config when needed

## Complete Example

### .env

```env
# Application
APP_NAME="My Application"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=root
DB_PASSWORD=secret

# Storage
FILESYSTEM_DISK=local
UPLOAD_MAX_SIZE=10240

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="${APP_NAME}"

# Third-party Services
STRIPE_KEY=pk_test_abc123
STRIPE_SECRET=sk_test_xyz789

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
```

### config/mail.php

```php
<?php

return [
    'default' => env('MAIL_MAILER', 'smtp'),

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],
];
```

### Usage in Code

```php
// Mail configuration
$mailConfig = config('mail');
$fromAddress = config('mail.from.address');
$smtpHost = config('mail.mailers.smtp.host');

// Application configuration
$appName = config('app.name');
$isDebug = config('app.debug');

// Custom service configuration
$stripeKey = config('services.stripe.key');
```

## Troubleshooting

### Configuration Not Loading

1. Check file exists in `config/` directory
2. Verify file returns an array
3. Clear configuration cache
4. Check for PHP syntax errors

### Environment Variables Not Working

1. Verify `.env` file exists
2. Check variable names match
3. Restart development server
4. Check `.env` file syntax

### Database Connection Failed

1. Verify database credentials in `.env`
2. Check database server is running
3. Test connection manually
4. Verify database exists

## Next Steps

- Learn about [Installation](installation.md)
- Understand [Directory Structure](directory-structure.md)
- Explore [Database](database.md)
