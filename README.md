## Flownest

Flownest is a content management system (CMS) designed for creating and managing websites with ease. It offers a user-friendly interface, customizable templates, and powerful features to help you build and maintain your online presence.

## 🚀 Quick Start

Get started in 5 minutes! See [QUICKSTART.md](./QUICKSTART.md)

```bash
git clone https://github.com/slym1v98/flownest.git
cd flownest
make install
```

## 📚 Documentation

- **[Quick Start Guide](./QUICKSTART.md)** - Get up and running in minutes
- **[Phase 5 Documentation](./PHASE5_DOCUMENTATION.md)** - Infrastructure, CI/CD, and monitoring
- **[Deployment Guide](./DEPLOYMENT_GUIDE.md)** - Production deployment instructions
- **[Testing Guide](./TESTING_GUIDE.md)** - Verify your infrastructure
- **[Docker Configuration](./docker/README.md)** - Docker setup and optimization

### Previous Phases

- **[Phase 4 Documentation](./PHASE4_DOCUMENTATION.md)** - Multi-language, RBAC, and workflow
- **[Phase 3 Documentation](./PHASE3_DOCUMENTATION.md)** - Content management features
- **[Phase 2 Features](./PHASE2_FEATURES.md)** - Core CMS functionality

## 🎯 Features

### Phase 5: Infrastructure & Production Ready ✅

- **Docker Infrastructure**
  - Multi-stage production builds
  - Nginx, PHP-FPM, MySQL, Redis, Meilisearch
  - Non-root security
  - Health checks and monitoring

- **CI/CD Pipeline**
  - Automated testing with PHPUnit
  - Code linting (Pint, ESLint, Prettier)
  - Automated deployment to production
  - Docker image building and publishing

- **Asset Optimization**
  - Vite with code splitting
  - CDN support (S3, DigitalOcean Spaces)
  - Gzip compression
  - Long-term caching

- **Monitoring & Error Tracking**
  - Laravel Pulse integration
  - Sentry error tracking
  - Health check endpoints
  - Performance monitoring

- **Performance Optimization**
  - PHP OPcache with JIT
  - Redis caching
  - MySQL tuning
  - Optimized for thousands of concurrent users

### Phase 4: Advanced Features ✅

- Multi-language content support
- Role-Based Access Control (RBAC)
- Approval workflow with notifications
- Complete audit trail with revision history

### Earlier Phases ✅

- Content management (posts, pages, categories)
- Media library with Spatie Media Library
- SEO optimization
- User authentication
- Admin dashboard with Vue.js + Inertia.js

## 🛠 Tech Stack

**Backend:**
- Laravel 12
- PHP 8.4
- MySQL 8.0
- Redis 7

**Frontend:**
- Vue.js 3
- TypeScript
- Tailwind CSS 4
- Inertia.js
- TipTap Editor

**Infrastructure:**
- Docker & Docker Compose
- Nginx
- Meilisearch
- GitHub Actions

**Monitoring:**
- Laravel Pulse
- Sentry

## 📦 Installation

### Development (with Docker)

```bash
# Clone repository
git clone https://github.com/slym1v98/flownest.git
cd flownest

# Using Makefile (recommended)
make install

# Or manually
cp .env.example .env
docker-compose up -d --build
docker-compose exec php-fpm composer install
docker-compose exec php-fpm php artisan key:generate
docker-compose exec php-fpm php artisan migrate
```

### Production Deployment

See [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md) for detailed instructions.

Quick production setup:
```bash
cp .env.production.example .env
# Edit .env with production settings
docker-compose up -d --build
make deploy
```

## 🧪 Testing

```bash
# Run all tests
docker-compose exec php-fpm php artisan test

# Using Makefile
make test

# Test infrastructure
./test-infrastructure.sh
```

See [TESTING_GUIDE.md](./TESTING_GUIDE.md) for comprehensive testing instructions.

## 📊 Monitoring

Access monitoring dashboards:
- **Health Check:** http://localhost/up
- **Laravel Pulse:** http://localhost/admin/pulse
- **PHP-FPM Status:** http://localhost/status

## 🔧 Common Commands

```bash
# Start services
make up

# Stop services
make down

# View logs
make logs

# Open shell
make shell

# Clear cache
make clear-cache

# Optimize for production
make optimize

# Backup database
make backup-db

# View all commands
make help
```

## 🔐 Security

- Non-root Docker containers
- Security headers enabled
- SSL/TLS support
- Protected sensitive files
- Secure session handling
- CSRF protection
- Input validation and sanitization

## 🚀 Performance

Optimized for production:
- PHP OPcache + JIT compilation
- Redis caching (sessions, cache, queues)
- MySQL query optimization
- Asset minification and code splitting
- Gzip compression
- Long-term browser caching
- CDN support

## 📈 Scalability

Built to scale:
- Horizontal scaling ready
- Load balancer compatible
- Shared storage support (S3/Spaces)
- Queue workers for background jobs
- Stateless application design

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

- Check documentation in the `docs/` directory
- Review [PHASE5_DOCUMENTATION.md](./PHASE5_DOCUMENTATION.md)
- See [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md) for deployment issues
- Check [TESTING_GUIDE.md](./TESTING_GUIDE.md) for testing problems

## 🎉 Credits

Built with Laravel, Vue.js, and modern web technologies.
