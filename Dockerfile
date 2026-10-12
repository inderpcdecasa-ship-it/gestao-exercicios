FROM richarvey/nginx-php-fpm:3.0.0

WORKDIR /var/www/html

# Copia os arquivos do projeto
COPY . .

# Instala dependências do Composer
RUN composer install --no-dev --optimize-autoloader

# Permissões
RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Define a pasta public como raiz do site
ENV NGINX_ROOT /var/www/html/public

EXPOSE 80