FROM richarvey/nginx-php-fpm:3.0.0

WORKDIR /var/www/html

# Copia todos os arquivos do projeto
COPY . .

# Instala as dependências do Laravel
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Remove qualquer index.php da raiz que atrapalhe
RUN rm -f /var/www/html/index.php

# ✅ Pasta correta do Laravel
ENV NGINX_DOCUMENT_ROOT /var/www/html/public

# ✅ PORTA QUE O RENDER EXIGE = 10000
ENV PORT=10000

# Permissões corretas
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Abre a porta certa
EXPOSE 10000