# Docker

This guide covers using Docker for development and deploying Nexus Framework in Docker containers.

## Table of Contents

- [Introduction](#introduction)
- [Docker Setup](#docker-setup)
- [Docker Compose](#docker-compose)
- [Development Workflow](#development-workflow)
- [Production Deployment](#production-deployment)
- [Docker Commands](#docker-commands)

## Introduction

Docker provides a consistent environment for your application across development, testing, and production. Nexus Framework comes with Docker configuration out of the box.

### Benefits

- **Consistency**: Same environment everywhere
- **Isolation**: No conflicts with other projects
- **Portability**: Easy to move between machines
- **Scalability**: Easy to scale services
- **Quick Setup**: Get started in minutes

## Docker Setup

### Prerequisites

Install Docker and Docker Compose:

**Ubuntu/Debian:**
```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Add user to docker group
sudo usermod -aG docker $USER
```

**Windows/Mac:**
- Download and install [Docker Desktop](https://www.docker.com/products/docker-desktop)

Verify installation:
```bash
docker --version
docker-compose --version
```

## Docker Compose

### Configuration File

`docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: nexus-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./docker/nginx.conf:/etc/nginx/sites-available/default
    ports:
      - "8000:80"
    networks:
      - nexus-network
    depends_on:
      - db

  db:
    image: mysql:8.0
    container_name: nexus-db
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: nexus
      MYSQL_USER: nexus
      MYSQL_PASSWORD: secret
    ports:
      - "3306:3306"
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - nexus-network

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: nexus-phpmyadmin
    restart: unless-stopped
    environment:
      PMA_HOST: db
      PMA_PORT: 3306
      PMA_USER: nexus
      PMA_PASSWORD: secret
    ports:
      - "8080:80"
    networks:
      - nexus-network
    depends_on:
      - db

networks:
  nexus-network:
    driver: bridge

volumes:
  dbdata:
    driver: local
```

### Dockerfile

`Dockerfile`:

```dockerfile
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copy Supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Install application dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Expose port 80
EXPOSE 80

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### Nginx Configuration

`docker/nginx.conf`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name localhost;
    root /var/www/public;

    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Supervisor Configuration

`docker/supervisord.conf`:

```ini
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
autostart=true
autorestart=true
stderr_logfile=/var/log/nginx/error.log
stdout_logfile=/var/log/nginx/access.log

[program:php-fpm]
command=/usr/local/sbin/php-fpm -F
autostart=true
autorestart=true
stderr_logfile=/var/log/php-fpm/error.log
stdout_logfile=/var/log/php-fpm/access.log
```

## Development Workflow

### Starting Development Environment

```bash
# Start containers
docker-compose up -d

# View logs
docker-compose logs -f

# Check running containers
docker-compose ps
```

Access services:
- **Application**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080
- **Database**: localhost:3306

### Stopping Environment

```bash
# Stop containers
docker-compose stop

# Stop and remove containers
docker-compose down

# Stop and remove everything (including volumes)
docker-compose down -v
```

### Running Commands

```bash
# Execute commands in app container
docker-compose exec app php nexus list
docker-compose exec app php nexus make:controller UserController
docker-compose exec app composer install

# Access container shell
docker-compose exec app bash

# MySQL database access
docker-compose exec db mysql -u nexus -psecret nexus
```

### File Permissions

Fix file permissions if needed:

```bash
# Inside app container
docker-compose exec app chown -R www-data:www-data storage
docker-compose exec app chmod -R 755 storage
```

## Production Deployment

### Production Dockerfile

`Dockerfile.prod`:

```dockerfile
FROM php:8.2-fpm as builder

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy application
COPY . .

# Final stage
FROM php:8.2-fpm

# Install production dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    nginx \
    supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Set working directory
WORKDIR /var/www

# Copy from builder
COPY --from=builder /var/www /var/www

# Copy configurations
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### Production Docker Compose

`docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    container_name: nexus-app-prod
    restart: always
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    volumes:
      - storage:/var/www/storage
      - logs:/var/www/storage/logs
    ports:
      - "80:80"
    networks:
      - nexus-network
    depends_on:
      - db

  db:
    image: mysql:8.0
    container_name: nexus-db-prod
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - nexus-network

networks:
  nexus-network:
    driver: bridge

volumes:
  storage:
    driver: local
  logs:
    driver: local
  dbdata:
    driver: local
```

### Deploy to Production

```bash
# Build production image
docker-compose -f docker-compose.prod.yml build

# Start production containers
docker-compose -f docker-compose.prod.yml up -d

# View logs
docker-compose -f docker-compose.prod.yml logs -f app

# Scale application
docker-compose -f docker-compose.prod.yml up -d --scale app=3
```

## Docker Commands

### Container Management

```bash
# List running containers
docker ps

# List all containers
docker ps -a

# Start container
docker start nexus-app

# Stop container
docker stop nexus-app

# Restart container
docker restart nexus-app

# Remove container
docker rm nexus-app

# Remove all stopped containers
docker container prune
```

### Image Management

```bash
# List images
docker images

# Build image
docker build -t nexus-app .

# Remove image
docker rmi nexus-app

# Remove unused images
docker image prune -a
```

### Logs and Debugging

```bash
# View container logs
docker logs nexus-app

# Follow logs
docker logs -f nexus-app

# Last 100 lines
docker logs --tail 100 nexus-app

# Execute command in container
docker exec -it nexus-app bash

# Inspect container
docker inspect nexus-app

# View container stats
docker stats nexus-app
```

### Volume Management

```bash
# List volumes
docker volume ls

# Inspect volume
docker volume inspect nexus_dbdata

# Remove volume
docker volume rm nexus_dbdata

# Remove unused volumes
docker volume prune
```

### Network Management

```bash
# List networks
docker network ls

# Inspect network
docker network inspect nexus-network

# Create network
docker network create nexus-network

# Remove network
docker network rm nexus-network
```

## Best Practices

### Development

1. **Use Volume Mounts**: Mount source code for live updates
2. **Separate Services**: Keep services in separate containers
3. **Use .dockerignore**: Exclude unnecessary files
4. **Named Volumes**: Use named volumes for data persistence
5. **Health Checks**: Add health checks to containers

### Production

1. **Multi-Stage Builds**: Reduce image size
2. **Non-Root User**: Run as non-root user
3. **Minimize Layers**: Combine RUN commands
4. **Security Scanning**: Scan images for vulnerabilities
5. **Resource Limits**: Set CPU and memory limits
6. **Logging**: Configure proper logging
7. **Monitoring**: Monitor container health

### .dockerignore

```.dockerignore
.git
.gitignore
.env
.env.*
node_modules
vendor
storage/logs/*
storage/framework/views/*
!storage/logs/.gitkeep
!storage/framework/views/.gitkeep
tests
.phpunit.result.cache
docker-compose.yml
docker-compose.prod.yml
README.md
```

## Troubleshooting

### Container Won't Start

```bash
# Check logs
docker-compose logs app

# Check container status
docker-compose ps

# Rebuild container
docker-compose up -d --build
```

### Permission Issues

```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data storage
docker-compose exec app chmod -R 755 storage
```

### Database Connection Issues

```bash
# Check database container
docker-compose ps db

# Check database logs
docker-compose logs db

# Test connection
docker-compose exec app php nexus test:db
```

### Port Already in Use

```bash
# Change ports in docker-compose.yml
ports:
  - "8001:80"  # Changed from 8000

# Or stop conflicting service
sudo systemctl stop apache2
```

## Next Steps

- Learn about [Deployment](deployment.md)
- Understand [Configuration](configuration.md)
- Explore [Security](security.md)
