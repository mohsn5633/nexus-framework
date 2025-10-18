# Installation

This guide will help you install and set up Nexus Framework on your local machine or server.

## System Requirements

Before installing Nexus Framework, ensure your system meets these requirements:

- **PHP**: 8.1 or higher
- **Composer**: Latest version
- **Extensions**: PDO, Mbstring, Fileinfo

Optional:
- **Database**: MySQL 5.7+, PostgreSQL 9.6+, or SQLite 3.8+
- **Docker**: For containerized development

## Installation Methods

### Method 1: Git Clone (Recommended for Development)

```bash
# Clone the repository
git clone https://github.com/nexus-framework/nexus.git my-project
cd my-project

# Install dependencies
composer install

# Configure environment
cp .env.example .env

# Generate application key (if needed)
# Edit .env file with your settings

# Create storage link
php nexus storage:link

# Start development server
php nexus serve
```

### Method 2: Composer Create-Project

```bash
# Create new project
composer create-project nexus/nexus my-project

# Navigate to project
cd my-project

# Configure environment
cp .env.example .env

# Create storage link
php nexus storage:link

# Start development server
php nexus serve
```

### Method 3: Docker

```bash
# Clone repository
git clone https://github.com/nexus-framework/nexus.git my-project
cd my-project

# Copy environment file
cp .env.example .env

# Start Docker containers
docker-compose up -d

# Install dependencies inside container
docker-compose exec app composer install

# Create storage link
docker-compose exec app php nexus storage:link
```

Visit `http://localhost:8000` (or your configured port).

## Configuration

### Environment File

Edit the `.env` file to configure your application:

```env
# Application
APP_NAME="Nexus Framework"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus
DB_USERNAME=root
DB_PASSWORD=

# File Storage
FILESYSTEM_DISK=local
UPLOAD_MAX_SIZE=10240
```

### Database Setup

Create your database:

```bash
# MySQL
mysql -u root -p
CREATE DATABASE nexus;
exit;

# PostgreSQL
psql -U postgres
CREATE DATABASE nexus;
\q
```

Update your `.env` file with database credentials.

### Directory Permissions

Ensure these directories are writable:

```bash
chmod -R 755 storage
chmod -R 755 storage/logs
chmod -R 755 storage/cache
chmod -R 755 storage/framework/views
chmod -R 755 public
```

On Windows, ensure your web server has write permissions to these directories.

### Web Server Configuration

#### Apache

Create or modify `.htaccess` in the `public` directory:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### Nginx

Add this to your Nginx configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/nexus/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Verification

Verify your installation is working:

```bash
# Check PHP version
php -v

# Check Composer version
composer --version

# List available commands
php nexus list

# Check routes
php nexus routes:list

# Start development server
php nexus serve
```

Visit `http://localhost:8000` - you should see the Nexus welcome page.

## Troubleshooting

### Issue: Permission Denied

**Solution**: Ensure storage and cache directories are writable:
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Issue: Class Not Found

**Solution**: Regenerate autoload files:
```bash
composer dump-autoload
```

### Issue: Port Already in Use

**Solution**: Use a different port:
```bash
php nexus serve --port=8080
```

### Issue: Database Connection Failed

**Solution**:
1. Check database credentials in `.env`
2. Ensure database server is running
3. Verify database exists
4. Test connection manually

### Issue: Storage Link Failed (Windows)

**Solution**: Run command prompt as Administrator:
```bash
php nexus storage:link
```

## Next Steps

Now that you have Nexus installed:

1. [Configure your application](configuration.md)
2. [Learn about routing](routing.md)
3. [Create your first controller](controllers.md)
4. [Set up your database](database.md)
5. [Build views with Blade](blade-templates.md)

## Updating Nexus

To update to the latest version:

```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer update

# Clear caches
php nexus view:clear

# Update storage link if needed
php nexus storage:link
```

## Uninstallation

To remove Nexus Framework:

```bash
# Stop any running servers
# Delete project directory
rm -rf /path/to/nexus

# Drop database (if needed)
mysql -u root -p -e "DROP DATABASE nexus;"
```
