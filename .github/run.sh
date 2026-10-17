#!/usr/bin/env bash
set -euo pipefail
exec </dev/null
cp .env.example .env
export CACHE_STORE=array QUEUE_CONNECTION=sync SESSION_DRIVER=array DB_CONNECTION=sqlite DB_DATABASE=:memory:
composer install --no-interaction --prefer-dist
composer require symfony/html-sanitizer --no-interaction
php artisan key:generate
set +e
php artisan test 2>&1 | tail -150
core=${PIPESTATUS[0]}
( cd contracts && composer install --no-interaction -q && vendor/bin/phpunit 2>&1 | tail -30 )
set -e
echo "core=$core"
exit $core
