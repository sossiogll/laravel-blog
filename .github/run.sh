#!/usr/bin/env bash
set -euo pipefail
exec </dev/null
cp .env.example .env
composer install --no-interaction --prefer-dist --no-progress >/dev/null 2>&1
php artisan key:generate >/dev/null
set +e
php artisan test 2>&1 | grep -v "✓" | head -150
grep -rn "activeTab" vendor/filament/filament/src/Resources/Pages/ -r | grep -i "url\|public" | head
