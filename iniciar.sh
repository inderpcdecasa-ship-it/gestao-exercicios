#!/bin/sh

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

php artisan migrate --force

# Cria o link simbólico para que as imagens apareçam corretamente na web
php artisan storage:link --force

# Garante permissão de leitura e execução em toda a árvore do projeto
chmod -R 755 /var/www/html

# Garante permissão de escrita nas pastas essenciais do Laravel
chmod -R 775 storage bootstrap/cache

php artisan config:clear
php artisan route:clear
php artisan cache:clear

exec /start.sh