# Flownest Production Deployment Guide

This guide walks you through deploying Flownest CMS to production using Docker.

## Prerequisites

- Ubuntu 20.04 LTS or newer (or similar Linux distribution)
- Docker and Docker Compose installed
- Domain name pointing to your server
- SSH access to the server
- At least 2GB RAM, 2 CPU cores, 20GB disk space

## Installation Steps

### 1. Install Docker

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user to docker group
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker --version
docker-compose --version
```

### 2. Clone Repository

```bash
# Create application directory
sudo mkdir -p /var/www/flownest
sudo chown $USER:$USER /var/www/flownest

# Clone repository
cd /var/www/flownest
git clone https://github.com/yourusername/flownest.git .
```

### 3. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit environment variables
nano .env
```

**Required Environment Variables:**

```env
# Application
APP_NAME=Flownest
APP_ENV=production
APP_KEY=base64:GENERATE_THIS
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database (Docker)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=flownest
DB_USERNAME=flownest
DB_PASSWORD=STRONG_PASSWORD_HERE
DB_ROOT_PASSWORD=STRONG_ROOT_PASSWORD_HERE

# Redis (Docker)
REDIS_HOST=redis
REDIS_PASSWORD=
REDIS_PORT=6379

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Meilisearch (Docker)
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=YOUR_MASTER_KEY_HERE

# Mail (configure your provider)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# AWS S3 or DigitalOcean Spaces (optional)
DO_SPACES_KEY=
DO_SPACES_SECRET=
DO_SPACES_REGION=nyc3
DO_SPACES_BUCKET=
DO_SPACES_ENDPOINT=https://nyc3.digitaloceanspaces.com

# Laravel Pulse
PULSE_ENABLED=true
PULSE_INGEST_DRIVER=database
PULSE_STORE_DRIVER=redis

# Sentry (optional)
SENTRY_LARAVEL_DSN=
SENTRY_TRACES_SAMPLE_RATE=0.2

# Docker ports
APP_PORT=80
APP_SSL_PORT=443
```

**Generate Application Key:**
```bash
docker-compose run --rm php-fpm php artisan key:generate
```

### 4. Build and Start Services

```bash
# Build Docker images
docker-compose build

# Start services
docker-compose up -d

# Verify all services are running
docker-compose ps
```

### 5. Initialize Application

```bash
# Install dependencies
docker-compose exec php-fpm composer install --no-dev --optimize-autoloader

# Run migrations
docker-compose exec php-fpm php artisan migrate --force

# Create storage link
docker-compose exec php-fpm php artisan storage:link

# Cache configuration
docker-compose exec php-fpm php artisan config:cache
docker-compose exec php-fpm php artisan route:cache
docker-compose exec php-fpm php artisan view:cache

# Seed database (optional)
docker-compose exec php-fpm php artisan db:seed
```

### 6. Setup SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Stop Nginx container temporarily
docker-compose stop nginx

# Obtain certificate
sudo certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com

# Copy certificates to Docker volume
sudo cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/
sudo cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/

# Update Nginx configuration
nano docker/nginx/conf.d/app.conf
# Uncomment SSL server block and update server_name

# Restart Nginx
docker-compose up -d nginx
```

**Setup Auto-renewal:**
```bash
# Test renewal
sudo certbot renew --dry-run

# Add cron job
sudo crontab -e
# Add this line:
0 0 * * * certbot renew --quiet --post-hook "docker-compose -f /var/www/flownest/docker-compose.yml restart nginx"
```

### 7. Configure Firewall

```bash
# Install UFW
sudo apt install ufw

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
sudo ufw status
```

## GitHub Actions Setup

### 1. Generate SSH Key

```bash
# On your local machine
ssh-keygen -t ed25519 -C "github-actions@yourdomain.com"

# Copy public key to server
ssh-copy-id -i ~/.ssh/id_ed25519.pub user@yourserver.com
```

### 2. Add GitHub Secrets

Go to: Repository → Settings → Secrets and variables → Actions

Add these secrets:
- `SSH_PRIVATE_KEY` - Contents of `~/.ssh/id_ed25519`
- `SERVER_HOST` - Your server IP or domain
- `SERVER_USER` - SSH username
- `SERVER_PATH` - `/var/www/flownest`
- `DOCKER_USERNAME` - Docker Hub username (optional)
- `DOCKER_PASSWORD` - Docker Hub password (optional)

### 3. Prepare Server for Deployments

```bash
# Create releases directory
mkdir -p /var/www/flownest/releases
mkdir -p /var/www/flownest/current

# Set permissions
chown -R $USER:$USER /var/www/flownest
```

### 4. Test Deployment

```bash
# Push to main branch
git add .
git commit -m "Deploy to production"
git push origin main

# Monitor GitHub Actions
# Go to: Repository → Actions
```

## Post-Deployment

### 1. Create Admin User

```bash
docker-compose exec php-fpm php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('your-secure-password'),
]);

$user->assignRole('Admin');
```

### 2. Configure Laravel Pulse

```bash
# Run Pulse migrations
docker-compose exec php-fpm php artisan vendor:publish --tag=pulse-migrations
docker-compose exec php-fpm php artisan migrate

# Access Pulse dashboard
# https://yourdomain.com/admin/pulse
```

### 3. Setup Backups

**Database Backup Script:**
```bash
#!/bin/bash
# /usr/local/bin/backup-flownest.sh

BACKUP_DIR="/var/backups/flownest"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup database
docker exec flownest-mysql mysqldump -u root -p$DB_ROOT_PASSWORD flownest | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup application files
tar -czf $BACKUP_DIR/app_$DATE.tar.gz /var/www/flownest/storage

# Keep only last 7 days
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

**Make executable and schedule:**
```bash
sudo chmod +x /usr/local/bin/backup-flownest.sh

# Add to crontab
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/backup-flownest.sh
```

### 4. Monitoring

**Check Service Status:**
```bash
docker-compose ps
docker-compose logs -f
```

**Monitor Resources:**
```bash
docker stats
```

**Check Application Logs:**
```bash
docker-compose exec php-fpm tail -f storage/logs/laravel.log
```

## Maintenance

### Update Application

```bash
cd /var/www/flownest

# Pull latest changes
git pull origin main

# Rebuild containers
docker-compose build

# Restart services
docker-compose up -d

# Run migrations
docker-compose exec php-fpm php artisan migrate --force

# Clear and cache
docker-compose exec php-fpm php artisan optimize
```

### Rollback Deployment

If something goes wrong:

```bash
# Stop current deployment
docker-compose down

# Restore from backup
cd /var/www/flownest/releases
# Find the backup: ls -la

# Copy backup to current
cp -r YYYYMMDD_HHMMSS/* ../current/

# Restart services
cd /var/www/flownest
docker-compose up -d
```

## Performance Tuning

### PHP-FPM Optimization

Edit `docker/php-fpm/www.conf`:

```ini
# For 2GB RAM server
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20

# For 4GB RAM server
pm.max_children = 100
pm.start_servers = 20
pm.min_spare_servers = 10
pm.max_spare_servers = 40
```

Restart PHP-FPM:
```bash
docker-compose restart php-fpm
```

### Redis Optimization

Edit `docker/redis/redis.conf`:

```conf
# Adjust based on available RAM
maxmemory 512mb
```

Restart Redis:
```bash
docker-compose restart redis
```

### MySQL Optimization

Edit `docker/mysql/my.cnf`:

```ini
# For larger datasets
innodb_buffer_pool_size = 2G
```

Restart MySQL:
```bash
docker-compose restart mysql
```

## Troubleshooting

### Services Won't Start

```bash
# Check logs
docker-compose logs

# Check specific service
docker-compose logs php-fpm
docker-compose logs nginx
docker-compose logs mysql

# Rebuild and restart
docker-compose down
docker-compose up -d --build
```

### Permission Errors

```bash
# Fix permissions
docker-compose exec php-fpm chown -R www:www storage bootstrap/cache
docker-compose exec php-fpm chmod -R 775 storage bootstrap/cache
```

### Database Connection Errors

```bash
# Check MySQL is running
docker-compose ps mysql

# Check credentials in .env
docker-compose exec mysql mysql -u flownest -p

# Restart database
docker-compose restart mysql
```

### 502 Bad Gateway

```bash
# Check PHP-FPM is running
docker-compose ps php-fpm

# Check logs
docker-compose logs php-fpm

# Restart PHP-FPM
docker-compose restart php-fpm
```

### High Memory Usage

```bash
# Check container stats
docker stats

# Clear cache
docker-compose exec php-fpm php artisan cache:clear
docker-compose exec php-fpm php artisan view:clear

# Restart services
docker-compose restart
```

## Security Best Practices

1. **Change all default passwords** in `.env`
2. **Enable HTTPS** with valid SSL certificate
3. **Set APP_DEBUG=false** in production
4. **Configure firewall** properly
5. **Regular security updates**:
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```
6. **Monitor logs** for suspicious activity
7. **Backup regularly** and test restores
8. **Use strong passwords** for database and admin
9. **Enable Redis authentication** if exposed
10. **Restrict Pulse dashboard** access

## Support Resources

- **Documentation:** [PHASE5_DOCUMENTATION.md](./PHASE5_DOCUMENTATION.md)
- **Laravel Docs:** https://laravel.com/docs
- **Docker Docs:** https://docs.docker.com
- **GitHub Actions:** https://docs.github.com/actions

## Conclusion

Your Flownest CMS is now deployed to production with:
- ✅ Docker orchestration
- ✅ SSL/TLS encryption
- ✅ Automated deployments
- ✅ Monitoring and error tracking
- ✅ Backup strategy
- ✅ Performance optimization

Monitor the application regularly and keep it updated!
