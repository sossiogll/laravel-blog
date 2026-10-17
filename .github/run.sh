set -euo pipefail
exec </dev/null

composer config name gllhub/gllhub
composer config description "gllHub: a content hub that keeps what has no other home, brings in what lives elsewhere and serves it."
composer config license MIT
composer config repositories.contracts '{"type": "path", "url": "contracts", "options": {"symlink": true}}'
composer config allow-plugins.wikimedia/composer-merge-plugin true
composer config extra.merge-plugin.include --json '["composer.local.json"]'
composer config extra.merge-plugin.merge-extra false
composer config minimum-stability stable
composer config prefer-stable true

composer require --no-progress --with-all-dependencies \
  "gllhub/contracts:*@dev" \
  "wikimedia/composer-merge-plugin:^2.1" \
  "filament/filament:^4.0" \
  "spatie/laravel-medialibrary:^11.0" \
  "spatie/laravel-backup:^9.0" \
  "laravel/horizon:^5.0"

php artisan install:api --no-interaction --without-migration-prompt || php artisan install:api --no-interaction
php artisan filament:install --panels --no-interaction
php artisan horizon:install --no-interaction
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations" --no-interaction
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider" --tag="backup-config" --no-interaction

php artisan about --only=environment || true
composer show --direct

rm -f database/database.sqlite .env
git add -A
git status --short | head -60
git commit -q -F - <<'MSG'
Add the core's dependencies

- gllhub/contracts from contracts/ (path repository).
- wikimedia/composer-merge-plugin: an instance lists its packages in a
  composer.local.json the core never reads by name.
- Filament (admin), Sanctum (API tokens), spatie/laravel-medialibrary,
  spatie/laravel-backup, Horizon (queues).

Installed with composer require and the packages' own install commands.

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_012PSSbsFxVXhZsAQfEukdKm
MSG
git log --oneline -1
