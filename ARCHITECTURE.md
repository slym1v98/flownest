# Flownest CMS Architecture

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                           Internet / Users                           │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                    ┌────────────▼────────────┐
                    │   SSL/TLS (Let's Encrypt) │
                    │   Port 443 (HTTPS)       │
                    └────────────┬────────────┘
                                 │
                    ┌────────────▼────────────┐
                    │   Nginx Web Server      │
                    │   - Reverse Proxy        │
                    │   - Gzip Compression     │
                    │   - Static Asset Caching │
                    │   - Security Headers     │
                    │   Port 80/443            │
                    └────────────┬────────────┘
                                 │
                    ┌────────────▼────────────┐
                    │   PHP-FPM 8.4           │
                    │   - OPcache + JIT       │
                    │   - Non-root (www:www)  │
                    │   - Laravel Application  │
                    │   Port 9000              │
                    └─────┬──────┬──────┬─────┘
                          │      │      │
        ┌─────────────────┘      │      └──────────────────┐
        │                        │                          │
┌───────▼────────┐    ┌─────────▼─────────┐    ┌──────────▼──────────┐
│ MySQL 8.0      │    │ Redis 7           │    │ Meilisearch         │
│ - InnoDB       │    │ - Cache           │    │ - Search Engine     │
│ - Buffer 1GB   │    │ - Sessions        │    │ - Full-text Search  │
│ - Optimized    │    │ - Queue           │    │ Port 7700           │
│ Port 3306      │    │ - LRU Eviction    │    └─────────────────────┘
└────────────────┘    │ Port 6379         │
                      └───────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                    Background Processes                              │
├─────────────────────────────────────────────────────────────────────┤
│ ┌─────────────────┐  ┌─────────────────┐  ┌────────────────────┐  │
│ │ Queue Worker    │  │ Scheduler       │  │ Laravel Pulse      │  │
│ │ - Async Jobs    │  │ - Cron Jobs     │  │ - Monitoring       │  │
│ │ - 3 Retries     │  │ - Every Minute  │  │ - Performance      │  │
│ └─────────────────┘  └─────────────────┘  └────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                    External Services (Optional)                      │
├─────────────────────────────────────────────────────────────────────┤
│ ┌─────────────────┐  ┌─────────────────┐  ┌────────────────────┐  │
│ │ AWS S3 / Spaces │  │ Sentry          │  │ SMTP Mail Server   │  │
│ │ - Media Storage │  │ - Error Track   │  │ - Notifications    │  │
│ │ - CDN           │  │ - Performance   │  │ - User Emails      │  │
│ └─────────────────┘  └─────────────────┘  └────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

## Docker Container Network

```
flownest-network (bridge)
├── nginx (flownest-nginx)
│   ├── Ports: 80:80, 443:443
│   └── Depends on: php-fpm
│
├── php-fpm (flownest-php)
│   ├── Port: 9000
│   ├── Volumes: ./:/var/www/html
│   └── Depends on: mysql, redis, meilisearch
│
├── mysql (flownest-mysql)
│   ├── Port: 3306
│   ├── Volume: mysql-data
│   └── Health check: mysqladmin ping
│
├── redis (flownest-redis)
│   ├── Port: 6379
│   ├── Volume: redis-data
│   └── Health check: redis-cli ping
│
├── meilisearch (flownest-meilisearch)
│   ├── Port: 7700
│   ├── Volume: meilisearch-data
│   └── Health check: /health endpoint
│
├── queue (flownest-queue)
│   └── Command: php artisan queue:work
│
└── scheduler (flownest-scheduler)
    └── Command: php artisan schedule:run (every 60s)
```

## Data Flow

### Request Flow
```
User Request
    │
    ▼
SSL/TLS Termination
    │
    ▼
Nginx (Port 80/443)
    │
    ├─▶ Static Files (cached, gzipped)
    │
    └─▶ PHP Request
            │
            ▼
        PHP-FPM (Port 9000)
            │
            ├─▶ OPcache (cached bytecode)
            │
            └─▶ Laravel Application
                    │
                    ├─▶ Redis (sessions, cache)
                    ├─▶ MySQL (data)
                    └─▶ Meilisearch (search)
                            │
                            ▼
                        Response (JSON/HTML)
                            │
                            ▼
                        Nginx (add headers)
                            │
                            ▼
                        User (gzipped)
```

### Queue Flow
```
Application
    │
    ▼
Dispatch Job ──▶ Redis Queue
                    │
                    ▼
                Queue Worker
                    │
                    ├─▶ Process Job
                    ├─▶ Retry (on failure, max 3)
                    └─▶ Mark Complete
```

### Caching Strategy
```
Request
    │
    ▼
Check Redis Cache
    │
    ├─▶ HIT ──▶ Return Cached Data
    │
    └─▶ MISS
            │
            ▼
        Query Database
            │
            ▼
        Store in Cache (TTL)
            │
            ▼
        Return Data
```

## CI/CD Pipeline Flow

```
Developer
    │
    └─▶ git push origin main
            │
            ▼
        GitHub Actions
            │
            ├─▶ Job 1: Lint
            │       ├─▶ Laravel Pint
            │       ├─▶ ESLint
            │       └─▶ Prettier
            │
            ├─▶ Job 2: Test (parallel)
            │       ├─▶ Setup PHP 8.4
            │       ├─▶ Setup PostgreSQL
            │       ├─▶ Setup Redis
            │       ├─▶ Run PHPUnit
            │       └─▶ Report Results
            │
            ├─▶ Job 3: Build Assets
            │       ├─▶ Setup Node.js
            │       ├─▶ npm ci
            │       ├─▶ npm run build
            │       └─▶ Upload Artifacts
            │
            ├─▶ Job 4: Deploy to Server
            │       ├─▶ Download Artifacts
            │       ├─▶ SSH to Server
            │       ├─▶ Backup Current
            │       ├─▶ Deploy New Code
            │       ├─▶ Run Migrations
            │       ├─▶ Cache Config
            │       └─▶ Restart Services
            │
            └─▶ Job 5: Docker Build (parallel)
                    ├─▶ Build Multi-arch Image
                    ├─▶ Tag (latest + SHA)
                    └─▶ Push to Registry
```

## Monitoring & Logging

```
Application Events
    │
    ├─▶ Laravel Pulse
    │       ├─▶ Cache Hits/Misses
    │       ├─▶ Slow Queries (>1s)
    │       ├─▶ Slow Requests (>1s)
    │       ├─▶ Queue Jobs
    │       ├─▶ Exceptions
    │       └─▶ Server Resources
    │
    ├─▶ Laravel Logs
    │       ├─▶ storage/logs/laravel.log
    │       └─▶ Rotated Daily
    │
    ├─▶ Sentry (optional)
    │       ├─▶ Exceptions
    │       ├─▶ Performance
    │       └─▶ User Context
    │
    └─▶ Docker Logs
            ├─▶ Nginx Access/Error
            ├─▶ PHP-FPM Logs
            ├─▶ MySQL Slow Query
            └─▶ Redis Logs
```

## Security Layers

```
┌────────────────────────────────────────────────────────┐
│ Layer 1: Network (Firewall)                           │
│ - UFW: Allow 80, 443, 22 only                         │
└────────────────────────────────────────────────────────┘
                        │
┌────────────────────────────────────────────────────────┐
│ Layer 2: SSL/TLS                                       │
│ - Let's Encrypt Certificates                           │
│ - TLS 1.2, 1.3 only                                    │
│ - HSTS Header                                          │
└────────────────────────────────────────────────────────┘
                        │
┌────────────────────────────────────────────────────────┐
│ Layer 3: Nginx                                         │
│ - Security Headers                                     │
│ - Protected Files (.env, etc.)                         │
│ - Rate Limiting (optional)                             │
└────────────────────────────────────────────────────────┘
                        │
┌────────────────────────────────────────────────────────┐
│ Layer 4: Docker                                        │
│ - Non-root User (www:www)                             │
│ - Isolated Network                                     │
│ - Resource Limits                                      │
└────────────────────────────────────────────────────────┘
                        │
┌────────────────────────────────────────────────────────┐
│ Layer 5: Laravel                                       │
│ - CSRF Protection                                      │
│ - XSS Prevention                                       │
│ - SQL Injection Protection                             │
│ - Authentication & Authorization                       │
└────────────────────────────────────────────────────────┘
```

## Scaling Strategy

### Horizontal Scaling

```
Load Balancer (Nginx/HAProxy)
    │
    ├─▶ App Server 1 (Docker)
    │       ├─▶ Nginx
    │       ├─▶ PHP-FPM
    │       └─▶ Queue Worker
    │
    ├─▶ App Server 2 (Docker)
    │       ├─▶ Nginx
    │       ├─▶ PHP-FPM
    │       └─▶ Queue Worker
    │
    └─▶ App Server N (Docker)
            ├─▶ Nginx
            ├─▶ PHP-FPM
            └─▶ Queue Worker

Shared Services:
├─▶ MySQL (Master-Replica)
├─▶ Redis (Cluster)
└─▶ S3/Spaces (Media)
```

### Vertical Scaling

```
Small Server (2GB RAM)
├─▶ PHP-FPM: max_children=50
├─▶ Redis: maxmemory=256mb
└─▶ MySQL: innodb_buffer_pool=1G

Medium Server (4GB RAM)
├─▶ PHP-FPM: max_children=100
├─▶ Redis: maxmemory=512mb
└─▶ MySQL: innodb_buffer_pool=2G

Large Server (8GB RAM)
├─▶ PHP-FPM: max_children=150
├─▶ Redis: maxmemory=1gb
└─▶ MySQL: innodb_buffer_pool=4G
```

## File Structure

```
flownest/
├── app/                    # Laravel application
├── config/                 # Configuration files
│   ├── filesystems.php     # S3/Spaces config
│   ├── pulse.php           # Monitoring config
│   └── services.php        # Sentry config
├── docker/                 # Docker configuration
│   ├── nginx/
│   │   └── conf.d/
│   │       └── app.conf
│   ├── php/
│   │   ├── php.ini
│   │   └── opcache.ini
│   ├── php-fpm/
│   │   └── www.conf
│   ├── redis/
│   │   └── redis.conf
│   ├── mysql/
│   │   └── my.cnf
│   └── scripts/
│       ├── php-fpm-healthcheck.sh
│       └── setup-ssl.sh
├── .github/
│   └── workflows/
│       └── deploy.yml      # CI/CD pipeline
├── Dockerfile              # Multi-stage build
├── docker-compose.yml      # Orchestration
├── Makefile                # Helper commands
├── vite.config.ts          # Asset build config
└── Documentation/
    ├── PHASE5_DOCUMENTATION.md
    ├── DEPLOYMENT_GUIDE.md
    ├── QUICKSTART.md
    ├── TESTING_GUIDE.md
    └── PHASE5_SUMMARY.md
```

## Resource Requirements

### Minimum (Development)
- CPU: 2 cores
- RAM: 4GB
- Disk: 10GB
- Concurrent Users: ~100

### Recommended (Production)
- CPU: 4 cores
- RAM: 8GB
- Disk: 50GB SSD
- Concurrent Users: ~1,000

### Optimal (High Traffic)
- CPU: 8+ cores
- RAM: 16GB+
- Disk: 100GB+ SSD
- Load Balancer: Yes
- Concurrent Users: 5,000+

---

**Legend:**
- ──▶ : Data flow
- │   : Connection
- └─▶ : Branch flow
- ├─▶ : Split flow
