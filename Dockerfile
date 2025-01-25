FROM php:7.4-apache

# Actualizar el sistema e instalar dependencias necesarias
RUN apt-get update && apt-get -y install python3 python3-pip sqlite3 libsqlite3-dev dos2unix \
    && python3 -m pip install pandas \
    && docker-php-ext-install pdo pdo_sqlite

# Configurar SSL
RUN openssl req -new -newkey rsa:4096 -days 3650 -nodes -x509 -subj \
    "/C=FR/ST=France/L=Bourges/O=INSACVL/CN=INSAnonym" \
    -keyout /etc/ssl/private/ssl-cert-snakeoil.key -out /etc/ssl/certs/ssl-cert-snakeoil.pem

# Habilitar módulos de Apache
RUN a2enmod ssl \
    && a2enmod rewrite \
    && a2dissite 000-default \
    && a2ensite default-ssl

# Configurar php.ini
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Exponer el puerto 443 para HTTPS
EXPOSE 443

# Copiar los archivos del proyecto al contenedor
COPY ./ /var/www/html

# Convertir a formato Unix y dar permisos de ejecución al script
RUN dos2unix /var/www/html/INSANONYM_STARTUP.sh && chmod +x /var/www/html/INSANONYM_STARTUP.sh

# Ejecutar el script de inicio y mantener Apache ejecutándose
CMD ["/bin/bash", "-c", "/var/www/html/INSANONYM_STARTUP.sh && apache2-foreground"]
