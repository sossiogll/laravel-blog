#!/usr/bin/env bash
set -euo pipefail
exec </dev/null
cp .env.example .env
export CACHE_STORE=array QUEUE_CONNECTION=sync SESSION_DRIVER=array
composer install --no-interaction --prefer-dist
composer require symfony/html-sanitizer --no-interaction
git diff --stat
git add composer.json composer.lock
git commit -q -m "Require symfony/html-sanitizer for the core's one body sanitizer

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_012PSSbsFxVXhZsAQfEukdKm"
git log --oneline -1
