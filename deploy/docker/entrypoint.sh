#!/bin/sh
set -e

if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

mkdir -p /var/www/html/database/data

if [ ! -f /var/www/html/database/data/database.sqlite ]; then
    if [ -f /var/www/html/database/database.sqlite ]; then
        cp /var/www/html/database/database.sqlite /var/www/html/database/data/database.sqlite
    else
        touch /var/www/html/database/data/database.sqlite
    fi
    chown www-data:www-data /var/www/html/database/data/database.sqlite
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
    php /var/www/html/artisan key:generate --force
fi

php /var/www/html/artisan migrate --force
php /var/www/html/artisan db:seed --force || true
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

exec /usr/bin/supervisord -c /etc/supervisord.conf
