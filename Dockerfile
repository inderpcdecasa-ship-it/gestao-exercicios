FROM richarvey/nginx-php-fpm:3.0.0

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80