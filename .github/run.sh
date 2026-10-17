#!/usr/bin/env bash
set -euo pipefail
exec </dev/null
cp .env.example .env
export CACHE_STORE=array QUEUE_CONNECTION=sync SESSION_DRIVER=array DB_CONNECTION=sqlite DB_DATABASE=:memory:
composer install --no-interaction --prefer-dist >/dev/null 2>&1
composer require symfony/html-sanitizer --no-interaction >/dev/null 2>&1
git add composer.json composer.lock
git commit -q -m 'Require symfony/html-sanitizer for the core body sanitizer' -- composer.json composer.lock || true
php artisan key:generate
set +e
php artisan test 2>&1 | tail -150
core=${PIPESTATUS[0]}
( cd contracts && composer install --no-interaction -q && vendor/bin/phpunit 2>&1 | tail -30 )
set -e
echo "core=$core"
exit $core
