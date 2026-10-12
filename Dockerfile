FROM richarvey/nginx-php-fpm:3.0.0

WORKDIR /var/www/html

# Copia os arquivos do projeto
COPY . .

# Remove qualquer index.php da raiz que possa atrapalhar
RUN rm -f /var/www/html/index.php

# Instala dependências
RUN composer install --no-dev --optimize-autoloader

# Permissões corretas para todos os arquivos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Define a pasta public como raiz do site
ENV NGINX_ROOT /var/www/html/public

EXPOSE 80