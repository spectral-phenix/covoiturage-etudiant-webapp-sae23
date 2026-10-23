# Serveur web : Apache + PHP 8.3 avec l'extension PDO MySQL
FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql
COPY app/ /var/www/html/
