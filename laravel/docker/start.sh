#!/bin/bash

php artisan key:generate --force
php artisan migrate --force
php artisan route:clear

php-fpm -D
nginx -g "daemon off;"
