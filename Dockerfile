# Multi-stage build for production-ready PHP-FPM with security and optimization
# Stage 1: Base dependencies
FROM php:8.4-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    postgresql-dev \
    icu-dev \
    oniguruma-dev \
    bash \
    curl \
    git \
    supervisor \
    nginx

# Configure PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    intl \
    mbstring \
    opcache \
    bcmath

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Stage 2: Composer dependencies
FROM base AS composer

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install production dependencies (no dev)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --optimize-autoloader

# Stage 3: Node.js build for assets
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install dependencies
RUN npm ci

# Copy source files needed for build
COPY vite.config.ts tsconfig.json eslint.config.js components.json ./
COPY resources ./resources
COPY public ./public

# Build assets
RUN npm run build

# Stage 4: Final production image
FROM base AS production

# Create non-root user
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

WORKDIR /var/www/html

# Copy application files
COPY --chown=www:www . .

# Copy composer dependencies from composer stage
COPY --from=composer --chown=www:www /var/www/html/vendor ./vendor

# Copy built assets from node-builder stage
COPY --from=node-builder --chown=www:www /app/public/build ./public/build

# Generate optimized autoloader
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# Configure PHP for production
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf

# Set proper permissions
RUN chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Switch to non-root user
USER www

# Expose PHP-FPM port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php-fpm-healthcheck || exit 1

CMD ["php-fpm"]
