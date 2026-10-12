#!/bin/sh
php artisan package:discover --ansi
php artisan config:clear
php artisan cache:clear
php artisan migrate --force
exec /start.sh