#!/bin/sh

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

php artisan migrate --force

# Garante permissão de leitura e execução em toda a árvore do projeto
chmod -R 755 /var/www/html

php artisan config:clear
php artisan route:clear
php artisan cache:clear

exec /start.sh