set -euo pipefail

composer create-project laravel/laravel:^12.0 /tmp/app --prefer-dist --no-interaction --no-progress --no-ansi
php /tmp/app/artisan --version
rm -rf /tmp/app/vendor /tmp/app/node_modules /tmp/app/.env /tmp/app/database/database.sqlite

# The old app leaves the tree (it stays in history and on master); the repository's own
# files stay: the workflows, the guide, the contract, the licence.
git ls-files | grep -Ev '^(\.github/|CLAUDE\.md$|contracts/|LICENSE$)' | xargs -r git rm -q
cp -a /tmp/app/. ./
git add -A
git commit -q -F - <<'MSG'
Replace the laravel-blog app with a Laravel 12 skeleton

The old app stays in the history and on master; models, migrations and
the v1 API are ported onto this skeleton in the commits that follow.
Generated with composer create-project laravel/laravel:^12.0.

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_012PSSbsFxVXhZsAQfEukdKm
MSG
git log --oneline -1
git show --stat HEAD | tail -5
