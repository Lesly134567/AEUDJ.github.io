# Usa la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instala las extensiones de PostgreSQL necesarias para conectar con Supabase
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copia el código de tu carpeta local al servidor
COPY . /var/www/html/

# Da permisos para que Apache pueda leer los archivos
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto 80
EXPOSE 80 