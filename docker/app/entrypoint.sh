#!/usr/bin/env bash
set -euo pipefail

# Only the service that sets BOOTSTRAP prepares the checkout; the others wait for
# it to be healthy (depends_on), so composer and the migrations never race.
if [ "${BOOTSTRAP:-0}" = "1" ]; then
    [ -f .env ] || cp .env.example .env
    composer install --no-interaction --prefer-dist
    grep -qE '^APP_KEY=.+' .env || php artisan key:generate --ansi
    php artisan migrate --force --ansi
fi

exec "$@"
