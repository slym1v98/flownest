#!/bin/bash

# SSL Certificate Setup Script for Flownest CMS
# This script helps setup Let's Encrypt SSL certificates

set -e

echo "═══════════════════════════════════════════════════"
echo "  Flownest CMS - SSL Certificate Setup"
echo "═══════════════════════════════════════════════════"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Please run as root (use sudo)"
    exit 1
fi

# Check if certbot is installed
if ! command -v certbot &> /dev/null; then
    echo "📦 Installing Certbot..."
    apt-get update
    apt-get install -y certbot python3-certbot-nginx
fi

# Get domain name
read -p "Enter your domain name (e.g., example.com): " DOMAIN

if [ -z "$DOMAIN" ]; then
    echo "❌ Domain name is required"
    exit 1
fi

# Get email
read -p "Enter your email address: " EMAIL

if [ -z "$EMAIL" ]; then
    echo "❌ Email address is required"
    exit 1
fi

# Confirm
echo ""
echo "Configuration:"
echo "  Domain: $DOMAIN"
echo "  Email: $EMAIL"
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Cancelled"
    exit 1
fi

# Stop nginx container
echo "🛑 Stopping Nginx container..."
docker-compose stop nginx || true

# Obtain certificate
echo "📜 Obtaining SSL certificate..."
certbot certonly --standalone \
    -d "$DOMAIN" \
    -d "www.$DOMAIN" \
    --email "$EMAIL" \
    --agree-tos \
    --non-interactive

if [ $? -ne 0 ]; then
    echo "❌ Failed to obtain certificate"
    docker-compose up -d nginx
    exit 1
fi

# Create SSL directory if it doesn't exist
mkdir -p docker/nginx/ssl

# Copy certificates
echo "📋 Copying certificates..."
cp /etc/letsencrypt/live/$DOMAIN/fullchain.pem docker/nginx/ssl/
cp /etc/letsencrypt/live/$DOMAIN/privkey.pem docker/nginx/ssl/

# Update nginx configuration
echo "⚙️  Updating Nginx configuration..."
NGINX_CONF="docker/nginx/conf.d/app.conf"

# Backup current config
cp "$NGINX_CONF" "$NGINX_CONF.backup"

# Update server_name in SSL block
sed -i "s/server_name your-domain.com;/server_name $DOMAIN www.$DOMAIN;/g" "$NGINX_CONF"

echo ""
echo "✅ SSL certificates installed successfully!"
echo ""
echo "Next steps:"
echo "1. Edit docker/nginx/conf.d/app.conf"
echo "2. Uncomment the SSL server block"
echo "3. Uncomment the HTTP to HTTPS redirect block"
echo "4. Run: docker-compose up -d nginx"
echo ""
echo "To test auto-renewal:"
echo "  sudo certbot renew --dry-run"
echo ""
echo "To setup auto-renewal cron job:"
echo "  sudo crontab -e"
echo "  Add: 0 0 * * * certbot renew --quiet --post-hook \"docker-compose -f $(pwd)/docker-compose.yml restart nginx\""
echo ""
