# Protocol Configuration (HTTP/HTTPS)

Configure and enforce HTTP or HTTPS protocol for all routes in your Nexus application.

## Table of Contents

- [Introduction](#introduction)
- [Configuration](#configuration)
- [Middleware](#middleware)
- [Examples](#examples)

## Introduction

The protocol configuration allows you to enforce HTTPS (or HTTP) across your entire application, automatically redirecting requests to the correct protocol.

### Features

- **Auto Mode**: No enforcement, accept both HTTP and HTTPS
- **HTTPS Enforcement**: Force all requests to use HTTPS
- **HTTP Enforcement**: Force HTTP (useful for local development)
- **Automatic Redirects**: Seamless 301 redirects to correct protocol

## Configuration

### Environment Variable

Configure protocol in `.env`:

```env
# Auto mode (default) - no enforcement
APP_PROTOCOL=auto

# Force HTTPS (recommended for production)
APP_PROTOCOL=https

# Force HTTP (for local development)
APP_PROTOCOL=http
```

### Configuration File

Protocol setting is in `config/app.php`:

```php
return [
    /*
    | Application Protocol
    |
    | Supported: "auto", "http", "https"
    | - auto: No enforcement
    | - http: Force HTTP
    | - https: Force HTTPS (recommended for production)
    */
    'protocol' => env('APP_PROTOCOL', 'auto'),
];
```

## Middleware

### Global Middleware

Apply protocol enforcement globally in your application bootstrap:

```php
use Nexus\Http\Middleware\EnforceProtocolMiddleware;

$router->middleware('protocol', EnforceProtocolMiddleware::class);
```

### Route Middleware

Apply to specific routes:

```php
use Nexus\Http\Middleware\EnforceProtocolMiddleware;

#[Get('/secure-page', 'secure')]
#[Middleware(EnforceProtocolMiddleware::class)]
public function securePage(): Response
{
    return Response::view('secure');
}
```

### Route Groups

Apply to route groups:

```php
$router->group([
    'middleware' => [EnforceProtocolMiddleware::class]
], function ($router) {
    // All routes in this group will enforce protocol
});
```

## Examples

### Production Setup (Force HTTPS)

**.env:**
```env
APP_ENV=production
APP_PROTOCOL=https
APP_URL=https://yourdomain.com
```

This configuration will:
- Force all HTTP requests to HTTPS
- Use 301 permanent redirects
- Ensure secure communication

### Local Development (Allow Both)

**.env:**
```env
APP_ENV=local
APP_PROTOCOL=auto
APP_URL=http://localhost:8000
```

This configuration will:
- Accept both HTTP and HTTPS
- No redirects
- Flexible for development

### Testing HTTPS Locally

**.env:**
```env
APP_ENV=local
APP_PROTOCOL=https
APP_URL=https://localhost:8443
```

Use with local SSL certificate for testing HTTPS behavior.

### Mixed Environment Setup

```php
<?php

namespace App\Middleware;

use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class ConditionalHttpsMiddleware extends Middleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        // Force HTTPS only for specific routes
        if ($this->requiresHttps($request)) {
            if (!$request->isSecure()) {
                return Response::redirect(
                    'https://' . $request->getHost() . $request->getRequestUri(),
                    301
                );
            }
        }

        return $next($request);
    }

    protected function requiresHttps(Request $request): bool
    {
        $httpsRoutes = [
            '/checkout',
            '/payment',
            '/account',
            '/admin',
        ];

        foreach ($httpsRoutes as $route) {
            if (str_starts_with($request->path(), $route)) {
                return true;
            }
        }

        return false;
    }
}
```

### API with HTTPS Enforcement

```php
<?php

namespace App\Controllers\Api;

use Nexus\Http\Request;
use Nexus\Http\Response;
use Nexus\Http\Middleware\EnforceProtocolMiddleware;
use Nexus\Http\Route\Get;

class ApiController
{
    #[Get('/api/users', 'api.users')]
    #[Middleware(EnforceProtocolMiddleware::class)]
    public function users(Request $request): Response
    {
        // This endpoint will only work over HTTPS
        if (!$request->isSecure()) {
            return Response::json([
                'error' => 'HTTPS required'
            ], 403);
        }

        $users = User::all();
        return Response::json($users);
    }
}
```

## Checking Protocol in Code

### Check if Request is Secure

```php
use Nexus\Http\Request;

public function index(Request $request): Response
{
    if ($request->isSecure()) {
        // Request is over HTTPS
    } else {
        // Request is over HTTP
    }
}
```

### Get Full URL with Protocol

```php
public function share(Request $request): Response
{
    $fullUrl = $request->fullUrl();
    // Returns: https://yourdomain.com/current/path

    return Response::view('share', [
        'shareUrl' => $fullUrl
    ]);
}
```

### Generate HTTPS URLs

```php
// In views or controllers
$httpsUrl = 'https://' . config('app.domain') . '/path';

// Or use asset helper (respects protocol)
$assetUrl = asset('images/logo.png');
```

## Best Practices

1. **Production HTTPS**: Always use HTTPS in production
2. **SSL Certificate**: Install valid SSL certificate
3. **HSTS Headers**: Add HTTP Strict Transport Security headers
4. **Mixed Content**: Ensure all resources load over HTTPS
5. **Redirect HTTP**: Use 301 permanent redirects
6. **Test Locally**: Test HTTPS setup before deploying
7. **Auto in Development**: Use 'auto' mode for local development

## Security Headers

Add security headers for HTTPS:

```php
<?php

namespace App\Middleware;

use Nexus\Http\Middleware;
use Nexus\Http\Request;
use Nexus\Http\Response;

class SecurityHeadersMiddleware extends Middleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        $response = $next($request);

        // Add security headers for HTTPS
        if ($request->isSecure()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
        }

        return $response;
    }
}
```

## SSL/TLS Setup

### Let's Encrypt (Free SSL)

```bash
# Install certbot
sudo apt-get install certbot

# Get certificate
sudo certbot certonly --webroot -w /var/www/yourapp/public -d yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    root /var/www/yourapp/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param HTTPS on;
    }
}
```

## Troubleshooting

### Redirect Loop

If you experience redirect loops:

1. Check protocol configuration in `.env`
2. Verify proxy/load balancer settings
3. Check `X-Forwarded-Proto` header
4. Ensure middleware is not applied multiple times

### Mixed Content Warnings

If browser shows mixed content warnings:

1. Use HTTPS for all asset URLs
2. Use protocol-relative URLs (`//domain.com/asset`)
3. Update hardcoded HTTP links
4. Check external resources

### SSL Certificate Issues

1. Verify certificate is valid and not expired
2. Check certificate chain is complete
3. Ensure certificate matches domain
4. Test with SSL checker tools

## Next Steps

- Learn about [Middleware](middleware.md)
- Understand [Configuration](configuration.md)
- Explore [Security](security.md)
