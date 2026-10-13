#!/bin/sh

# 1. Garante que existe uma APP_KEY
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# 2. Executa as migrações na base de dados PostgreSQL
php artisan migrate --force

# 3. Limpa caches
php artisan config:clear
php artisan cache:clear

# 4. Inicia o servidor web do Render
exec /start.sh