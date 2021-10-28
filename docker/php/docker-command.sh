#!/bin/sh

rm -r /var/www/app/var/cache/*
composer --working-dir=/var/www/app dump-env prod --ansi
php /var/www/app/bin/console cache:clear
php /var/www/app/bin/console cache:pool:clear cache.global_clearer
php /var/www/app/bin/console doctrine:migrations:migrate --no-interaction

php-fpm