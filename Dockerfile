FROM richarvey/nginx-php-fpm:3.0.0

WORKDIR /var/www/html

# Copia os arquivos
COPY . .

# Instala dependências
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 🔑 CORREÇÃO PRINCIPAL: garante dono e permissões CORRETAS
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod +x /var/www/html/public/index.php

# Remove qualquer index.php da RAIZ que atrapalhe
RUN rm -f /var/www/html/index.php

# Força o Nginx a usar a pasta public
ENV NGINX_ROOT /var/www/html/public

EXPOSE 80