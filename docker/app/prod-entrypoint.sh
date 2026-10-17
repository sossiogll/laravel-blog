#!/bin/sh
set -eu

# bootstrap/cache lives in each container's own filesystem, so each one warms its own.
php artisan config:cache
php artisan route:cache
php artisan event:cache

exec "$@"
