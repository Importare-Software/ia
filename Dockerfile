# Utiliza la imagen base de PHP 8.3 con FPM
FROM php:8.3-fpm

# Define los argumentos para el usuario y UID
ARG user=ubuntu
ARG uid=1000

# Actualiza los paquetes e instala las dependencias necesarias para PHP y otras herramientas útiles
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nano \
    libwebp-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev

# Limpia el caché de apt para reducir el tamaño de la imagen
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Configura y habilita la extensión de PHP para manejar archivos ZIP
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip

# Instala las librerías de imagen necesarias y configura la extensión GD de PHP para soporte de imágenes
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd

# Instala la extensión de PHP mbstring para soporte de cadenas multibyte
RUN apt-get update && apt-get install -y libonig-dev && docker-php-ext-install mbstring

# Instala las extensiones de PHP necesarias: bcmath para cálculos matemáticos, exif para leer metadatos de imágenes y pdo_mysql para soporte de bases de datos MySQL
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install exif
RUN docker-php-ext-install pdo_mysql

# Copia Composer desde la imagen oficial de Composer y lo coloca en el contenedor
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crea un nuevo usuario con los grupos www-data y root, y establece su directorio home
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Instala la extensión de PHP Redis y la habilita
RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

# Establece el directorio de trabajo
WORKDIR /var/www

# Copia un archivo de configuración PHP personalizado
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Instala Node.js y npm
RUN apt update && apt install -y nodejs npm

# Copia el script de inicio al contenedor y le da permisos de ejecución
COPY startup.sh /var/www/startup.sh
RUN chmod +x /var/www/startup.sh

# Cambia al usuario creado previamente
USER $user
