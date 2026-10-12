#!/usr/bin/env bash
echo "Instalando dependências..."
composer install --no-dev --working-dir=/var/www/html

echo "Cache de configuração..."
php artisan config:cache

echo "Cache de rotas..."
php artisan route:cache

echo "Executando migrações..."
php artisan migrate --force

echo "Iniciando servidor..."
exec /usr/local/bin/start.sh