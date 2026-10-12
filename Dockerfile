FROM richarvey/nginx-php-fpm:3.0.0

WORKDIR /var/www/html

# Copia os arquivos do projeto
COPY . .

# Instala dependências do Composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Remove qualquer index.php da raiz que atrapalhe
RUN rm -f /var/www/html/index.php

# Copia nossa configuração personalizada do Nginx
COPY nginx-laravel.conf /etc/nginx/sites-available/default.conf

# Permissões CORRETAS
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80