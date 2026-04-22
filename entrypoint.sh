#!/bin/sh
chown www-data:www-data /var/www/html/config.json /var/www/html/shifts.json
exec apache2-foreground
