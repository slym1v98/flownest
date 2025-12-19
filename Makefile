# Flownest CMS - Production Management Commands
# 
# Usage:
#   make help          - Show this help message
#   make build         - Build Docker images
#   make up            - Start all services
#   make down          - Stop all services
#   make restart       - Restart all services
#   make logs          - View logs from all services
#   make shell         - Open shell in PHP container

.PHONY: help build up down restart logs shell deploy optimize backup restore clean

help: ## Show this help message
	@echo "Flownest CMS - Production Management"
	@echo ""
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Build Docker images
	docker-compose build

up: ## Start all services
	docker-compose up -d
	@echo "Services started. Access at http://localhost"

down: ## Stop all services
	docker-compose down

restart: ## Restart all services
	docker-compose restart

logs: ## View logs from all services (press Ctrl+C to exit)
	docker-compose logs -f

shell: ## Open shell in PHP container
	docker-compose exec php-fpm bash

mysql: ## Open MySQL shell
	docker-compose exec mysql mysql -u root -p

redis-cli: ## Open Redis CLI
	docker-compose exec redis redis-cli

deploy: ## Deploy application (run migrations, cache, etc.)
	docker-compose exec php-fpm php artisan migrate --force
	docker-compose exec php-fpm php artisan config:cache
	docker-compose exec php-fpm php artisan route:cache
	docker-compose exec php-fpm php artisan view:cache
	docker-compose exec php-fpm php artisan queue:restart
	@echo "Deployment completed"

optimize: ## Optimize application for production
	docker-compose exec php-fpm php artisan optimize
	docker-compose exec php-fpm php artisan config:cache
	docker-compose exec php-fpm php artisan route:cache
	docker-compose exec php-fpm php artisan view:cache
	docker-compose exec php-fpm php artisan event:cache
	@echo "Application optimized"

clear-cache: ## Clear all caches
	docker-compose exec php-fpm php artisan optimize:clear
	docker-compose exec php-fpm php artisan cache:clear
	docker-compose exec php-fpm php artisan config:clear
	docker-compose exec php-fpm php artisan route:clear
	docker-compose exec php-fpm php artisan view:clear
	@echo "All caches cleared"

test: ## Run tests
	docker-compose exec php-fpm php artisan test

lint: ## Run code linting
	docker-compose exec php-fpm ./vendor/bin/pint

npm-install: ## Install NPM dependencies
	docker-compose run --rm node npm install

npm-build: ## Build frontend assets
	docker-compose run --rm node npm run build

backup-db: ## Backup database
	@mkdir -p backups
	docker exec flownest-mysql mysqldump -u root -p flownest > backups/db-$$(date +%Y%m%d_%H%M%S).sql
	@echo "Database backed up to backups/"

backup-files: ## Backup application files
	@mkdir -p backups
	tar -czf backups/files-$$(date +%Y%m%d_%H%M%S).tar.gz storage/app
	@echo "Files backed up to backups/"

restore-db: ## Restore database from backup (usage: make restore-db FILE=backup.sql)
	@if [ -z "$(FILE)" ]; then \
		echo "Error: Please specify FILE=path/to/backup.sql"; \
		exit 1; \
	fi
	docker exec -i flownest-mysql mysql -u root -p flownest < $(FILE)
	@echo "Database restored from $(FILE)"

status: ## Show status of all services
	docker-compose ps

stats: ## Show resource usage of containers
	docker stats --no-stream

clean: ## Clean up dangling images and stopped containers
	docker system prune -f
	@echo "Cleaned up Docker resources"

update: ## Update application (pull, build, migrate)
	git pull origin main
	docker-compose build
	docker-compose up -d
	docker-compose exec php-fpm composer install --no-dev --optimize-autoloader
	docker-compose exec php-fpm php artisan migrate --force
	docker-compose exec php-fpm php artisan optimize
	@echo "Application updated"

install: ## Initial installation
	@echo "Installing Flownest CMS..."
	cp .env.example .env
	@echo "Please edit .env file with your configuration"
	@read -p "Press enter when .env is configured..."
	docker-compose up -d --build
	docker-compose exec php-fpm composer install --no-dev --optimize-autoloader
	docker-compose exec php-fpm php artisan key:generate
	docker-compose exec php-fpm php artisan migrate --force
	docker-compose exec php-fpm php artisan storage:link
	docker-compose exec php-fpm php artisan optimize
	@echo "Installation completed! Access at http://localhost"

health: ## Check health of all services
	@echo "Checking service health..."
	@docker-compose ps | grep -q "healthy" && echo "✓ All services healthy" || echo "✗ Some services unhealthy"
	curl -sf http://localhost/up > /dev/null && echo "✓ Application responding" || echo "✗ Application not responding"
