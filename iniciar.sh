#!/bin/sh
php artisan config:cache
php artisan route:cache
exec /start.sh