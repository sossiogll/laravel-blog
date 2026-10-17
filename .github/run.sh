#!/usr/bin/env bash
set -euo pipefail
exec </dev/null
cp .env.example .env
composer install --no-interaction --prefer-dist --no-progress >/dev/null 2>&1
set +e
V=vendor/filament
echo "### button.css"; sed -n 1,140p $V/support/resources/css/components/button.css
echo "### isLight usage"; grep -rn "isLight\|contrast" $V/support/src $V/filament/src --include=*.php -l | head; grep -rn "isLight\|findShade" $V/support/src/Colors/ColorManager.php | head
echo "### navitems"; grep -rn "function getNavigationItems\|function getUrl(" $V/filament/src | head
echo "### create body"; sed -n 56,135p $V/filament/src/Resources/Pages/CreateRecord.php; sed -n 300,335p $V/filament/src/Resources/Pages/CreateRecord.php
echo "### edit body"; sed -n 85,135p $V/filament/src/Resources/Pages/EditRecord.php;  sed -n 155,205p $V/filament/src/Resources/Pages/EditRecord.php; sed -n 360,382p $V/filament/src/Resources/Pages/EditRecord.php
echo "### list"; sed -n 55,135p $V/filament/src/Resources/Pages/ListRecords.php; sed -n 215,230p $V/filament/src/Resources/Pages/ListRecords.php
echo "### Resource.getNavigationItems"; grep -rn "getNavigationItems" $V/filament/src | head
echo "### routes"; grep -rn "function route(" -A12 $V/filament/src/Resources/Pages/Page.php | head -40
