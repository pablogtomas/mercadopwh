# Imagen oficial de PHP con Apache
FROM php:8.1-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Copiar archivos al directorio de Apache
COPY . /var/www/html/

# Habilitar mod_rewrite (por si lo necesitas después)
RUN a2enmod rewrite

# Puerto por defecto
EXPOSE 80
