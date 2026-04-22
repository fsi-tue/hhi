FROM php:8.3-apache

WORKDIR /var/www/html

# Enable .htaccess support
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite

COPY . .

RUN chown -R www-data:www-data /var/www/html

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
