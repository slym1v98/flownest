# Flownest CMS - Quick Start Guide

Get Flownest CMS up and running in minutes!

## Prerequisites

- Git
- Docker & Docker Compose
- 4GB RAM minimum
- 10GB free disk space

## Quick Installation (5 minutes)

### 1. Clone Repository

```bash
git clone https://github.com/slym1v98/flownest.git
cd flownest
```

### 2. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit configuration (minimal required)
nano .env
```

**Minimal Configuration:**
```env
APP_NAME=Flownest
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_DATABASE=flownest
DB_USERNAME=flownest
DB_PASSWORD=secret
DB_ROOT_PASSWORD=root_secret
```

### 3. Start Services

```bash
# Build and start all services
docker-compose up -d --build

# Wait for services to be ready (30-60 seconds)
docker-compose ps
```

### 4. Initialize Application

```bash
# Install dependencies
docker-compose exec php-fpm composer install

# Generate application key
docker-compose exec php-fpm php artisan key:generate

# Run migrations
docker-compose exec php-fpm php artisan migrate

# Create storage link
docker-compose exec php-fpm php artisan storage:link

# Cache configuration
docker-compose exec php-fpm php artisan config:cache
```

### 5. Create Admin User

```bash
docker-compose exec php-fpm php artisan tinker
```

In Tinker console:
```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@flownest.test',
    'password' => bcrypt('password')
]);

$user->assignRole('Admin');
exit
```

### 6. Access Application

Open your browser:
- **Frontend:** http://localhost
- **Admin:** http://localhost/admin
- **Pulse Dashboard:** http://localhost/admin/pulse

**Login Credentials:**
- Email: admin@flownest.test
- Password: password

## Using Makefile (Easier)

We provide a Makefile for common tasks:

```bash
# Install everything automatically
make install

# View all available commands
make help

# Start services
make up

# View logs
make logs

# Open shell
make shell

# Run tests
make test

# Optimize for production
make optimize
```

## Development Workflow

### Start Development Server

```bash
# Using Docker Compose
docker-compose up -d

# Using Makefile
make up
```

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f php-fpm

# Using Makefile
make logs
```

### Run Commands

```bash
# Artisan commands
docker-compose exec php-fpm php artisan [command]

# Composer
docker-compose exec php-fpm composer [command]

# NPM (for asset building)
npm install
npm run dev
```

### Access Shell

```bash
# PHP container
docker-compose exec php-fpm bash

# Using Makefile
make shell
```

### Database Access

```bash
# MySQL shell
docker-compose exec mysql mysql -u flownest -p

# Using Makefile
make mysql
```

### Redis Access

```bash
# Redis CLI
docker-compose exec redis redis-cli

# Using Makefile
make redis-cli
```

## Common Tasks

### Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### Run Tests

```bash
docker-compose exec php-fpm php artisan test

# Using Makefile
make test
```

### Clear Caches

```bash
docker-compose exec php-fpm php artisan optimize:clear

# Using Makefile
make clear-cache
```

### Backup Database

```bash
make backup-db
```

### Stop Services

```bash
docker-compose down

# Using Makefile
make down
```

## Services Overview

After starting, you'll have:

| Service | Port | Description |
|---------|------|-------------|
| Nginx | 80 | Web server |
| PHP-FPM | 9000 | Application |
| MySQL | 3306 | Database |
| Redis | 6379 | Cache & Sessions |
| Meilisearch | 7700 | Search engine |

## Troubleshooting

### Services Won't Start

```bash
# Check logs
docker-compose logs

# Rebuild
docker-compose down
docker-compose up -d --build
```

### Port Already in Use

```bash
# Stop conflicting services
sudo systemctl stop nginx
sudo systemctl stop mysql

# Or change ports in .env
APP_PORT=8080
DB_PORT=3307
```

### Permission Errors

```bash
# Fix permissions
docker-compose exec php-fpm chown -R www:www storage bootstrap/cache
docker-compose exec php-fpm chmod -R 775 storage bootstrap/cache
```

### Database Connection Error

```bash
# Wait for MySQL to be ready
docker-compose exec mysql mysqladmin ping -h localhost

# Verify credentials in .env match docker-compose.yml
```

### Can't Access Application

```bash
# Check services are running
docker-compose ps

# Check health
curl http://localhost/up

# View nginx logs
docker-compose logs nginx
```

## Next Steps

1. **Configure Email**: Update mail settings in `.env`
2. **Setup Storage**: Configure S3 or DigitalOcean Spaces
3. **Enable Monitoring**: Install Laravel Pulse (`composer require laravel/pulse`)
4. **Setup Sentry**: Add Sentry for error tracking
5. **Read Documentation**: Check [PHASE5_DOCUMENTATION.md](./PHASE5_DOCUMENTATION.md)
6. **Deployment**: Follow [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md)

## Useful Commands

```bash
# View all available make commands
make help

# Check service health
make health

# View resource usage
make stats

# Update application
make update

# Optimize for production
make optimize
```

## Getting Help

- **Documentation:** [PHASE5_DOCUMENTATION.md](./PHASE5_DOCUMENTATION.md)
- **Deployment:** [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md)
- **Docker Config:** [docker/README.md](./docker/README.md)
- **Laravel Docs:** https://laravel.com/docs
- **Docker Docs:** https://docs.docker.com

## Development Tips

### Watch for Changes

```bash
# In one terminal - watch PHP changes
docker-compose logs -f php-fpm

# In another terminal - watch frontend changes
npm run dev
```

### Quick Reset

```bash
# Reset everything
docker-compose down -v
docker-compose up -d --build
make deploy
```

### Debug Mode

Enable debug mode in `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

View logs:
```bash
docker-compose exec php-fpm tail -f storage/logs/laravel.log
```

## Production Deployment

For production deployment, see [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md)

Quick checklist:
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure strong passwords
- [ ] Setup SSL/TLS
- [ ] Configure firewall
- [ ] Setup backups
- [ ] Configure monitoring

Happy coding! 🚀
