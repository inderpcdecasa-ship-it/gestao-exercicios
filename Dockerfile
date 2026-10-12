FROM richarvey/nginx-php-fpm:3.0.0

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN rm -f /var/www/html/index.php

ENV WEBROOT /var/www/html/public
ENV PORT=10000

COPY iniciar.sh /iniciar.sh
RUN chmod +x /iniciar.sh

# Remove configs antigas do Nginx da imagem e copia a personalizada
RUN rm -f /etc/nginx/sites-enabled/default.conf /etc/nginx/conf.d/default.conf
COPY nginx.conf /etc/nginx/sites-enabled/default.conf

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 10000

CMD ["/iniciar.sh"]