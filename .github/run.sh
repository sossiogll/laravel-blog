#!/usr/bin/env bash
set -euo pipefail
exec </dev/null
cp .env.example .env
composer install --no-interaction --prefer-dist --no-progress >/dev/null 2>&1
php artisan key:generate >/dev/null
set +e
php artisan test 2>&1 | grep -v "✓" | grep -v "^#[0-9]" | grep -v "^\s*$" | cut -c1-260 | head -60
