# Docker Configuration Files

This directory contains production-ready Docker configuration files for Flownest CMS.

## Directory Structure

```
docker/
├── nginx/
│   ├── conf.d/
│   │   └── app.conf          # Nginx server configuration
│   └── ssl/                   # SSL certificates (not in git)
├── php/
│   ├── php.ini                # Production PHP configuration
│   └── opcache.ini            # OPcache optimization settings
├── php-fpm/
│   └── www.conf               # PHP-FPM pool configuration
├── redis/
│   └── redis.conf             # Redis production settings
└── mysql/
    └── my.cnf                 # MySQL performance tuning
```

## Configuration Files

### Nginx (`nginx/conf.d/app.conf`)

**Features:**
- HTTP/2 support
- Gzip compression
- Static asset caching (1 year)
- Security headers
- FastCGI connection to PHP-FPM
- Health check endpoint
- SSL/TLS configuration (commented, ready to enable)

**SSL Setup:**
1. Place certificates in `nginx/ssl/` directory
2. Uncomment SSL server block in `app.conf`
3. Update `server_name` with your domain
4. Restart Nginx: `docker-compose restart nginx`

### PHP (`php/php.ini`)

**Key Settings:**
- Memory limit: 256MB
- Upload max filesize: 20MB
- Post max size: 24MB
- Max execution time: 300s
- Error logging enabled
- Display errors disabled (production)

**Security:**
- `expose_php = Off`
- `allow_url_include = Off`
- Secure session cookies

### OPcache (`php/opcache.ini`)

**Optimization:**
- Memory consumption: 256MB
- Max accelerated files: 20,000
- Validation timestamps: Disabled (production)
- JIT compilation: Enabled (tracing mode)
- JIT buffer: 128MB

**Performance:**
- 3-5x faster PHP execution
- Reduced CPU usage
- Better throughput

### PHP-FPM (`php-fpm/www.conf`)

**Process Management:**
- Pool: dynamic
- Max children: 50
- Start servers: 10
- Min/max spare: 5/20

**Monitoring:**
- Access log enabled
- Slow log enabled (>5s)
- Status page: `/status`
- Ping page: `/ping`

**Tuning for Different Server Sizes:**

**2GB RAM:**
```ini
pm.max_children = 50
pm.start_servers = 10
```

**4GB RAM:**
```ini
pm.max_children = 100
pm.start_servers = 20
```

**8GB RAM:**
```ini
pm.max_children = 150
pm.start_servers = 30
```

### Redis (`redis/redis.conf`)

**Features:**
- LRU eviction policy
- 256MB memory limit
- AOF persistence
- Slow log enabled
- Connection optimization

**Security:**
- Uncomment `requirepass` for password protection
- Bind to specific IP if needed

**Performance:**
- Fast key-value operations
- Automatic memory management
- Persistence for durability

### MySQL (`mysql/my.cnf`)

**InnoDB Optimization:**
- Buffer pool: 1GB
- Log file size: 256MB
- Flush method: O_DIRECT

**Connection Settings:**
- Max connections: 200
- Wait timeout: 600s

**Logging:**
- Slow query log enabled (>2s)
- Performance schema enabled

**Character Set:**
- UTF-8 (utf8mb4) by default

## Customization

### Adjusting PHP Memory

Edit `php/php.ini`:
```ini
memory_limit = 512M  ; Increase if needed
```

Restart PHP-FPM:
```bash
docker-compose restart php-fpm
```

### Adjusting PHP-FPM Workers

Edit `php-fpm/www.conf`:
```ini
pm.max_children = 100  ; Increase for more traffic
```

Formula: `max_children = (Total RAM - Other Services) / (PHP Process Size)`

Average PHP process size: ~50MB

### Adjusting Redis Memory

Edit `redis/redis.conf`:
```ini
maxmemory 512mb  ; Increase if needed
```

Restart Redis:
```bash
docker-compose restart redis
```

### Adjusting MySQL Buffer Pool

Edit `mysql/my.cnf`:
```ini
innodb_buffer_pool_size = 2G  ; 50-70% of available RAM
```

Restart MySQL:
```bash
docker-compose restart mysql
```

## Monitoring

### Check PHP-FPM Status

```bash
docker exec flownest-nginx curl http://php-fpm:9000/status
```

### Check OPcache Status

Create a PHP file in public directory:
```php
<?php
phpinfo();
```

Access via browser and search for "OPcache"

### Check Redis Memory Usage

```bash
docker exec flownest-redis redis-cli INFO memory
```

### Check MySQL Performance

```bash
docker exec flownest-mysql mysql -u root -p -e "SHOW STATUS LIKE 'Slow_queries';"
```

## Troubleshooting

### PHP-FPM Slow Performance

1. Check slow log:
```bash
docker exec flownest-php cat /var/log/www.slow.log
```

2. Increase workers in `php-fpm/www.conf`

3. Enable OPcache (verify it's loaded)

### Redis Memory Issues

1. Check memory usage:
```bash
docker exec flownest-redis redis-cli INFO memory
```

2. Increase `maxmemory` in `redis.conf`

3. Adjust eviction policy if needed

### MySQL Slow Queries

1. Check slow query log:
```bash
docker exec flownest-mysql tail -f /var/log/mysql/slow.log
```

2. Add database indexes

3. Increase `innodb_buffer_pool_size`

### Nginx 502 Bad Gateway

1. Check PHP-FPM is running:
```bash
docker-compose ps php-fpm
```

2. Check PHP-FPM logs:
```bash
docker-compose logs php-fpm
```

3. Increase `fastcgi_read_timeout` in `nginx/conf.d/app.conf`

## Best Practices

1. **Regular Updates:** Keep Docker images updated
2. **Monitor Logs:** Check logs daily for errors
3. **Backup Configs:** Keep backup of configuration files
4. **Test Changes:** Test configuration changes in staging first
5. **Resource Monitoring:** Monitor CPU, RAM, and disk usage
6. **Security:** Keep passwords secure, use SSL in production

## Production Checklist

- [ ] SSL/TLS certificates configured
- [ ] PHP-FPM workers tuned for server specs
- [ ] OPcache enabled and configured
- [ ] Redis memory limit set appropriately
- [ ] MySQL buffer pool optimized
- [ ] Slow query logs reviewed
- [ ] Security headers enabled
- [ ] Error reporting disabled in PHP
- [ ] Nginx gzip compression enabled
- [ ] Health checks configured
- [ ] Backups automated

## Support

For issues or questions:
- Check [PHASE5_DOCUMENTATION.md](../PHASE5_DOCUMENTATION.md)
- Review [DEPLOYMENT_GUIDE.md](../DEPLOYMENT_GUIDE.md)
- Check official Docker documentation
