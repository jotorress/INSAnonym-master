FROM php:7.4-apache

RUN apt-get update
RUN apt-get -y install python3 python3-pip sqlite3 libsqlite3-dev
RUN python3 -m pip install pandas
RUN docker-php-ext-install pdo pdo_sqlite

RUN openssl req -new -newkey rsa:4096 -days 3650 -nodes -x509 -subj \
    "/C=FR/ST=France/L=Bourges/O=INSACVL/CN=INSAnonym" \
    -keyout /etc/ssl/private/ssl-cert-snakeoil.key -out /etc/ssl/certs/ssl-cert-snakeoil.pem

RUN a2enmod ssl
RUN a2enmod rewrite
RUN a2dissite 000-default
RUN a2ensite default-ssl
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY ./ /var/www/html
RUN chmod +x /var/www/html/INSANONYM_STARTUP.sh
CMD /var/www/html/INSANONYM_STARTUP.sh
