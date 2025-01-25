#!/bin/bash
cd /var/www/html
chown www-data:www-data -R ./
chmod 0740 -R ./
apache2-foreground
