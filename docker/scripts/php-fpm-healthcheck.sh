#!/bin/sh
# PHP-FPM Health Check Script
# This script checks if PHP-FPM is running and responding

set -e

# Check if PHP-FPM is running
if ! pgrep -x php-fpm > /dev/null; then
    echo "PHP-FPM is not running"
    exit 1
fi

# Check if PHP-FPM can execute commands
if ! php -v > /dev/null 2>&1; then
    echo "PHP is not responding"
    exit 1
fi

# Try to connect to PHP-FPM via TCP
if command -v cgi-fcgi > /dev/null 2>&1; then
    REQUEST_METHOD=GET \
    SCRIPT_NAME=/ping \
    SCRIPT_FILENAME=/ping \
    cgi-fcgi -bind -connect 127.0.0.1:9000 > /dev/null 2>&1
    
    if [ $? -ne 0 ]; then
        echo "PHP-FPM is not accepting connections"
        exit 1
    fi
fi

echo "PHP-FPM is healthy"
exit 0
