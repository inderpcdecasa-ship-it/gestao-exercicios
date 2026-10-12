#!/bin/sh

# 1. Garante que existe uma APP_KEY
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# 2. Executa as migrações para criar as tabelas (como 'sessions', 'users', etc.)
php artisan migrate --force

# 3. Limpa caches antigas para evitar conflitos
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# 4. Inicia o servidor web do Render
exec /start.sh