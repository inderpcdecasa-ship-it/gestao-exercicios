FROM richarvey/nginx-php-fpm:3.0.0

WORKDIR /var/www/html

# Copia os arquivos
COPY . .

# Instala dependências
RUN composer install --no-dev --optimize-autoloader

# Permissões
RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Script de inicialização
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80
CMD ["/usr/local/bin/start.sh"]