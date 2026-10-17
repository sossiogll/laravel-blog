#!/usr/bin/env bash
set -euo pipefail
exec </dev/null

cp .env.example .env
composer install --no-interaction --prefer-dist --no-progress >/dev/null 2>&1
set +e
V=vendor/filament
echo "### theme mode"; grep -rn "function defaultThemeMode\|function darkMode" $V/filament/src/Panel/Concerns/ | head
echo "### font"; grep -rn "function font" $V/filament/src/Panel/Concerns/ | head
echo "### color.php 580-700"; sed -n 575,700p $V/support/src/Colors/Color.php
echo "### css"; ls $V/support/resources/css $V/support/resources/css/components 2>&1 | head -40
grep -rln "fi-btn" $V/*/resources/css | head
echo "### Resource fns"; grep -n "function \(getNavigationItems\|getUrl\|getPages\|form\|table\|getEloquentQuery\|getNavigation\|getPluralModelLabel\|canCreate\|getRecordTitle\)" $V/filament/src/Resources/Resource.php $V/filament/src/Resources/Concerns/*.php
echo "### createrecord"; grep -n "function " $V/filament/src/Resources/Pages/CreateRecord.php
echo "### edit"; grep -n "function " $V/filament/src/Resources/Pages/EditRecord.php
echo "### list"; grep -n "function " $V/filament/src/Resources/Pages/ListRecords.php
echo "### spatie upload"; sed -n 1,90p $V/spatie-laravel-media-library-plugin/src/Forms/Components/SpatieMediaLibraryFileUpload.php
echo "### navitem"; grep -n "function " $V/filament/src/Navigation/NavigationItem.php
