# Testing Infrastructure Components

This guide helps you verify that all Phase 5 infrastructure components are working correctly.

## Prerequisites

Make sure all services are running:
```bash
docker-compose ps
```

All services should show "Up" and "healthy" status.

## 1. Docker Infrastructure Tests

### Test Docker Services

```bash
# Check all containers are running
docker-compose ps

# Expected output: All services should be "Up" with "(healthy)" status
```

### Test Nginx

```bash
# Test Nginx configuration
docker-compose exec nginx nginx -t

# Should output: "test is successful"

# Test HTTP access
curl -I http://localhost

# Should return: HTTP/1.1 200 OK

# Test health check endpoint
curl http://localhost/up

# Should return: OK
```

### Test PHP-FPM

```bash
# Check PHP version
docker-compose exec php-fpm php -v

# Should show: PHP 8.4.x

# Check OPcache is loaded
docker-compose exec php-fpm php -i | grep opcache.enable

# Should show: opcache.enable => On => On

# Test PHP-FPM status
curl http://localhost/status

# Should return status information
```

### Test MySQL

```bash
# Connect to MySQL
docker-compose exec mysql mysql -u flownest -psecret -e "SELECT 1;"

# Should return: 1

# Check database exists
docker-compose exec mysql mysql -u flownest -psecret -e "SHOW DATABASES LIKE 'flownest';"

# Should list: flownest
```

### Test Redis

```bash
# Test Redis connection
docker-compose exec redis redis-cli ping

# Should return: PONG

# Test set/get
docker-compose exec redis redis-cli SET test_key "test_value"
docker-compose exec redis redis-cli GET test_key

# Should return: test_value
```

### Test Meilisearch

```bash
# Check Meilisearch health
curl http://localhost:7700/health

# Should return: {"status":"available"}

# Get Meilisearch version
curl http://localhost:7700/version

# Should return version information
```

## 2. Application Tests

### Test Laravel Application

```bash
# Run artisan commands
docker-compose exec php-fpm php artisan --version

# Should show Laravel version

# Test database connection
docker-compose exec php-fpm php artisan db:show

# Should show database information

# Test cache
docker-compose exec php-fpm php artisan cache:clear
docker-compose exec php-fpm php artisan config:cache

# Should complete without errors
```

### Run Test Suite

```bash
# Run PHPUnit tests
docker-compose exec php-fpm php artisan test

# Should run all tests and show results
```

### Test Queue Worker

```bash
# Check queue worker is running
docker-compose ps queue

# Should show: Up

# Dispatch a test job
docker-compose exec php-fpm php artisan tinker
```

In Tinker:
```php
dispatch(function () {
    logger('Test job executed');
});
exit
```

Check logs:
```bash
docker-compose exec php-fpm tail -f storage/logs/laravel.log
```

## 3. Performance Tests

### Test OPcache

```bash
# Check OPcache statistics
docker-compose exec php-fpm php -r "print_r(opcache_get_status());"

# Should show cache statistics with hits > 0 after some requests
```

### Test Redis Performance

```bash
# Benchmark Redis
docker-compose exec redis redis-cli --intrinsic-latency 10

# Should show latency statistics (lower is better)
```

### Test Response Times

```bash
# Benchmark application
apt-get install -y apache2-utils  # if not installed

ab -n 1000 -c 10 http://localhost/up

# Check "Requests per second" - should be high (>1000)
```

## 4. Asset Optimization Tests

### Test Vite Build

```bash
# Build assets
npm run build

# Check output directory
ls -lh public/build/

# Should see optimized JS/CSS files

# Check file sizes
du -h public/build/assets/*.js

# Should be reasonably sized with code splitting
```

### Test Compression

```bash
# Test gzip compression
curl -H "Accept-Encoding: gzip" -I http://localhost/

# Should show: Content-Encoding: gzip
```

### Test Asset Caching

```bash
# Check cache headers for assets
curl -I http://localhost/build/assets/app.js

# Should show: Cache-Control: public, max-age=31536000
```

## 5. Security Tests

### Test Security Headers

```bash
# Check security headers
curl -I http://localhost

# Should include:
# X-Frame-Options: SAMEORIGIN
# X-Content-Type-Options: nosniff
# X-XSS-Protection: 1; mode=block
```

### Test Protected Files

```bash
# Try to access .env
curl http://localhost/.env

# Should return: 403 Forbidden or 404 Not Found

# Try to access composer.json
curl http://localhost/composer.json

# Should return: 403 Forbidden or 404 Not Found
```

### Test Non-root Execution

```bash
# Check PHP-FPM user
docker-compose exec php-fpm whoami

# Should return: www (not root)

# Check file permissions
docker-compose exec php-fpm ls -la /var/www/html

# Files should be owned by www:www
```

## 6. Monitoring Tests

### Test Laravel Pulse (if installed)

```bash
# Install Pulse if not already installed
docker-compose exec php-fpm composer require laravel/pulse

# Run migrations
docker-compose exec php-fpm php artisan vendor:publish --tag=pulse-migrations
docker-compose exec php-fpm php artisan migrate

# Access Pulse dashboard
curl http://localhost/admin/pulse

# Should return HTML (status 200)
```

### Test Error Tracking

```bash
# Trigger a test error
docker-compose exec php-fpm php artisan tinker
```

In Tinker:
```php
throw new Exception('Test exception');
exit
```

Check if error was logged:
```bash
docker-compose exec php-fpm tail -f storage/logs/laravel.log
```

## 7. CI/CD Pipeline Tests

### Test GitHub Actions Locally

If you have `act` installed:
```bash
# Install act
curl https://raw.githubusercontent.com/nektos/act/master/install.sh | sudo bash

# Run tests locally
act -j test
```

### Simulate Deployment

```bash
# Test deployment commands manually
docker-compose exec php-fpm composer install --no-dev --optimize-autoloader
docker-compose exec php-fpm php artisan migrate --force
docker-compose exec php-fpm php artisan config:cache
docker-compose exec php-fpm php artisan route:cache
docker-compose exec php-fpm php artisan view:cache
docker-compose exec php-fpm php artisan queue:restart

# All should complete without errors
```

## 8. Load Testing

### Simple Load Test

```bash
# Install wrk
apt-get install -y wrk

# Run load test
wrk -t4 -c100 -d30s http://localhost/up

# Check results:
# - Requests/sec should be high
# - Latency should be low
# - No errors
```

### Stress Test

```bash
# Gradually increase load
for i in 1 2 4 8 16 32; do
    echo "Testing with $i threads..."
    wrk -t$i -c$((i*25)) -d10s http://localhost/up
    sleep 5
done

# Monitor resources
docker stats
```

## 9. Backup & Restore Tests

### Test Database Backup

```bash
# Create backup
make backup-db

# Verify backup file exists
ls -lh backups/

# Should see recent .sql file
```

### Test Database Restore

```bash
# Create test data
docker-compose exec php-fpm php artisan tinker
# In Tinker: User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('test')]);

# Backup
make backup-db

# Drop database
docker-compose exec mysql mysql -u root -proot_secret -e "DROP DATABASE flownest; CREATE DATABASE flownest;"

# Restore
make restore-db FILE=backups/[your-backup-file].sql

# Verify data is restored
docker-compose exec php-fpm php artisan tinker
# In Tinker: User::where('email', 'test@test.com')->first();
```

## 10. Logs and Debugging

### Check All Logs

```bash
# Application logs
docker-compose logs php-fpm | tail -100

# Web server logs
docker-compose logs nginx | tail -100

# Database logs
docker-compose logs mysql | tail -100

# Queue worker logs
docker-compose logs queue | tail -100

# Redis logs
docker-compose logs redis | tail -100
```

### Monitor in Real-time

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f php-fpm
```

## Verification Checklist

Use this checklist to verify your infrastructure:

- [ ] All Docker containers running and healthy
- [ ] Nginx serving requests on port 80
- [ ] PHP-FPM responding with correct version (8.4)
- [ ] MySQL accepting connections
- [ ] Redis responding to PING
- [ ] Meilisearch accessible on port 7700
- [ ] OPcache enabled and working
- [ ] Application accessible via browser
- [ ] Health check endpoint (/up) responding
- [ ] PHPUnit tests passing
- [ ] Queue worker processing jobs
- [ ] Assets built and optimized
- [ ] Gzip compression working
- [ ] Security headers present
- [ ] Protected files not accessible
- [ ] Running as non-root user (www)
- [ ] Logs being written correctly
- [ ] Backup/restore working
- [ ] Performance acceptable under load

## Troubleshooting Common Issues

### Container Won't Start

```bash
# Check logs
docker-compose logs [service-name]

# Rebuild
docker-compose up -d --build [service-name]
```

### Test Failures

```bash
# Clear all caches
docker-compose exec php-fpm php artisan optimize:clear

# Re-run migrations
docker-compose exec php-fpm php artisan migrate:fresh

# Re-run tests
docker-compose exec php-fpm php artisan test
```

### Performance Issues

```bash
# Check resource usage
docker stats

# Restart services
docker-compose restart

# Clear OPcache
docker-compose restart php-fpm
```

## Automated Testing Script

Save this as `test-infrastructure.sh`:

```bash
#!/bin/bash

echo "Testing Flownest Infrastructure..."

# Test services
docker-compose ps | grep -q "Up" && echo "✓ Containers running" || echo "✗ Containers not running"
curl -sf http://localhost/up > /dev/null && echo "✓ Application responding" || echo "✗ Application not responding"
docker-compose exec -T redis redis-cli ping | grep -q PONG && echo "✓ Redis working" || echo "✗ Redis not working"
docker-compose exec -T mysql mysql -u flownest -psecret -e "SELECT 1;" > /dev/null 2>&1 && echo "✓ MySQL working" || echo "✗ MySQL not working"

echo "Testing complete!"
```

Make it executable and run:
```bash
chmod +x test-infrastructure.sh
./test-infrastructure.sh
```

## Conclusion

If all tests pass, your infrastructure is ready for production! 🚀

For issues, check:
- [Troubleshooting section in PHASE5_DOCUMENTATION.md](./PHASE5_DOCUMENTATION.md#troubleshooting)
- [Docker README](./docker/README.md#troubleshooting)
- Service logs: `docker-compose logs [service]`
