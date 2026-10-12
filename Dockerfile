FROM richarvey/nginx-php-fpm:3.0.0

WORKDIR /var/www/html

# Copia os arquivos do projeto
COPY . .

# Instala dependências do Composer
RUN composer install --no-dev --optimize-autoloader

# Permissões
RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Porta
EXPOSE 80