#!/bin/sh
set -eu

install -d -o www-data -g www-data -m 0770 /var/www/generated

exec docker-php-entrypoint "$@"
