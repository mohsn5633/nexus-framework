# Deployment

This guide covers deploying your Nexus Framework application to production servers.

## Table of Contents

- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [Server Requirements](#server-requirements)
- [Environment Configuration](#environment-configuration)
- [Web Server Setup](#web-server-setup)
- [Deployment Methods](#deployment-methods)
- [Post-Deployment](#post-deployment)
- [Security](#security)

## Pre-Deployment Checklist

Before deploying, ensure you've completed these tasks:

- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Configure production database credentials
- [ ] Set strong `APP_KEY` for encryption
- [ ] Review and update all configuration files
- [ ] Remove development dependencies
- [ ] Test application thoroughly
- [ ] Set up error logging
- [ ] Configure file permissions
- [ ] Set up SSL certificate
- [ ] Configure backup strategy

## Server Requirements

### Minimum Requirements

- **PHP**: 8.1 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Database**: MySQL 5.7+, PostgreSQL 9.6+, or SQLite 3.8+
- **Composer**: Latest version
- **SSL Certificate**: Required for production

### PHP Extensions

Required extensions:
- PDO
- Mbstring
- Fileinfo
- OpenSSL
- JSON
- Ctype

Check installed extensions:
```bash
php -m
```

## Environment Configuration

### Production .env File

Create `.env` file with production settings:

```env
# Application
APP_NAME="Your Application"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=production_db
DB_USERNAME=db_user
DB_PASSWORD=strong_password_here

# File Storage
FILESYSTEM_DISK=local
UPLOAD_MAX_SIZE=10240

# Session
SESSION_LIFETIME=120
SESSION_SECURE=true
SESSION_HTTP_ONLY=true

# Security
APP_KEY=generate_strong_random_key_here
```

### Environment Variables

Never commit `.env` to version control:

```bash
# .gitignore
.env
.env.production
.env.local
```

## Web Server Setup

### Apache Configuration

#### .htaccess

Ensure `.htaccess` exists in `public/` directory:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### Virtual Host

Configure Apache virtual host:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com

    DocumentRoot /var/www/yourapp/public

    <Directory /var/www/yourapp/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/yourapp-error.log
    CustomLog ${APACHE_LOG_DIR}/yourapp-access.log combined

    # Redirect to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com

    DocumentRoot /var/www/yourapp/public

    <Directory /var/www/yourapp/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/ca_bundle.crt

    ErrorLog ${APACHE_LOG_DIR}/yourapp-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/yourapp-ssl-access.log combined
</VirtualHost>
```

Enable required modules:
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

### Nginx Configuration

Create Nginx server block:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;

    server_name yourdomain.com www.yourdomain.com;
    root /var/www/yourapp/public;

    index index.php index.html;

    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Logging
    access_log /var/log/nginx/yourapp-access.log;
    error_log /var/log/nginx/yourapp-error.log;

    # Serve static files
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Test and reload Nginx:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

## Deployment Methods

### Method 1: Manual Deployment via FTP/SFTP

```bash
# 1. Upload files to server
# Use FTP client or command line

# 2. SSH into server
ssh user@yourserver.com

# 3. Navigate to application directory
cd /var/www/yourapp

# 4. Install dependencies
composer install --no-dev --optimize-autoloader

# 5. Set permissions
chmod -R 755 storage
chmod -R 755 storage/logs
chmod -R 755 storage/framework/views

# 6. Create storage link
php nexus storage:link
```

### Method 2: Git Deployment

```bash
# On server
cd /var/www

# Clone repository
git clone https://github.com/yourusername/yourapp.git
cd yourapp

# Checkout production branch
git checkout production

# Install dependencies
composer install --no-dev --optimize-autoloader

# Copy environment file
cp .env.example .env
nano .env  # Edit with production settings

# Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Create storage link
php nexus storage:link
```

### Method 3: Automated Deployment (CI/CD)

#### GitHub Actions Example

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v2

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'

    - name: Install Dependencies
      run: composer install --no-dev --optimize-autoloader

    - name: Deploy to Server
      uses: appleboy/scp-action@master
      with:
        host: ${{ secrets.SERVER_HOST }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SERVER_SSH_KEY }}
        source: "."
        target: "/var/www/yourapp"

    - name: Run Post-Deployment Commands
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.SERVER_HOST }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SERVER_SSH_KEY }}
        script: |
          cd /var/www/yourapp
          php nexus view:clear
          chmod -R 755 storage
```

## Post-Deployment

### File Permissions

Set correct permissions:

```bash
# Directories
chmod -R 755 storage
chmod -R 755 storage/logs
chmod -R 755 storage/cache
chmod -R 755 storage/framework/views

# Make storage writable
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
```

### Clear Caches

```bash
# Clear view cache
php nexus view:clear

# Clear application cache (if implemented)
php nexus cache:clear
```

### Verify Installation

Test the deployed application:

```bash
# Check if application is accessible
curl https://yourdomain.com

# Check database connection
php nexus test:db

# List routes
php nexus routes:list
```

## Security

### Security Checklist

- [ ] HTTPS enabled with valid SSL certificate
- [ ] `APP_DEBUG=false` in production
- [ ] Strong database passwords
- [ ] Firewall configured (UFW/iptables)
- [ ] SSH key authentication only
- [ ] Regular security updates
- [ ] File upload validation
- [ ] CSRF protection enabled
- [ ] SQL injection prevention (use query builder)
- [ ] XSS protection (escape output)

### SSL Certificate (Let's Encrypt)

Install Certbot:

```bash
sudo apt install certbot python3-certbot-nginx

# For Nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# For Apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### Firewall Configuration

```bash
# UFW (Ubuntu)
sudo ufw allow 22      # SSH
sudo ufw allow 80      # HTTP
sudo ufw allow 443     # HTTPS
sudo ufw enable

# Check status
sudo ufw status
```

### Security Headers

Add to Nginx configuration:

```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' https:;" always;
```

## Monitoring & Maintenance

### Log Monitoring

```bash
# Monitor error logs
tail -f storage/logs/app.log

# Monitor web server logs
tail -f /var/log/nginx/yourapp-error.log
```

### Scheduled Tasks (Cron)

Set up cron jobs:

```bash
# Edit crontab
crontab -e

# Add scheduled tasks
0 2 * * * cd /var/www/yourapp && php nexus emails:send
0 3 * * * cd /var/www/yourapp && php nexus db:cleanup --days=30
0 4 * * 0 cd /var/www/yourapp && php nexus backup:database
```

### Database Backups

```bash
# MySQL backup
mysqldump -u username -p database_name > backup-$(date +%Y%m%d).sql

# Automate with cron
0 2 * * * mysqldump -u username -ppassword database_name > /backups/db-$(date +\%Y\%m\%d).sql
```

## Troubleshooting

### Common Issues

**500 Internal Server Error**
- Check error logs
- Verify file permissions
- Check `.env` configuration

**404 Not Found**
- Check web server configuration
- Verify DocumentRoot path
- Check .htaccess rewrite rules

**Database Connection Error**
- Verify database credentials
- Check database server is running
- Test connection manually

**Permission Denied**
- Check file/directory permissions
- Verify owner/group settings
- Check SELinux (if applicable)

## Next Steps

- Learn about [Docker Deployment](docker.md)
- Understand [Configuration](configuration.md)
- Set up [Monitoring](monitoring.md)
