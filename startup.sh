#!/bin/sh

# Instalamos las dependecias
composer install

# Compilamos los archivos de Vue
#npm run build


#rm -rf public/storage
#php artisan storage:link

# Limpieza de Cache
#php artisan optimize:clear

# Inicia PHP-FPM
php-fpm
