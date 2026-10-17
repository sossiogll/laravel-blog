#!/usr/bin/env bash
set -euo pipefail
exec </dev/null
git clone -q --depth 1 https://github.com/sossiogll/gllhub-packages /tmp/pk
sed 's#/gllhub-packages/#/tmp/pk/#' composer.local.json.example > composer.local.json
cp .env.example .env
export CACHE_STORE=array QUEUE_CONNECTION=sync SESSION_DRIVER=array
composer update gllhub/portfolio gllhub/medium --no-interaction
grep -n '"name": "gllhub/' -A1 composer.lock
php artisan key:generate
php artisan test
git status --short
