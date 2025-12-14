#!/bin/bash

php artisan key:generate --force
php artisan migrate --force

php-fpm -D
nginx -g "daemon off;"
