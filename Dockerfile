FROM php:8.4.6-apache

COPY docker/php.ini /usr/local/etc/php/

COPY docker/apache.conf /etc/apache2/sites-enabled/000-default.conf 

COPY index.php /var/www/html

WORKDIR /var/www/html