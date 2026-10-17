#!/usr/bin/env bash
set -euo pipefail
exec </dev/null
composer update gllhub/contracts --no-interaction --no-scripts
grep -n '"name": "gllhub/contracts"' -A6 composer.lock
git add composer.lock
git commit -q -m "lock"
# Dry check of the optional-packages flow, if the packages repo is readable.
if git clone -q --depth 1 https://github.com/sossiogll/gllhub-packages /tmp/pk; then
  cp composer.json /tmp/c.json; cp composer.lock /tmp/c.lock
  sed 's#/gllhub-packages/#/tmp/pk/#' > composer.local.json <<'J'
{"repositories":[{"type":"path","url":"/gllhub-packages/*","options":{"symlink":true}}],
 "require":{"gllhub/portfolio":"*@dev","gllhub/medium":"*@dev"}}
J
  composer update gllhub/portfolio gllhub/medium --no-interaction --no-scripts 2>&1 | tail -20
  grep -n '"name": "gllhub/' composer.lock
  rm composer.local.json; cp /tmp/c.json composer.json; cp /tmp/c.lock composer.lock
else
  echo "packages repo not cloneable: skipped"
fi
