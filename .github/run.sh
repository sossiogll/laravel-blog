#!/usr/bin/env bash
set -euo pipefail
exec </dev/null

cp .env.example .env
composer install --no-interaction --prefer-dist --no-progress
composer require filament/spatie-laravel-media-library-plugin:^4.0 --no-interaction --no-progress -W
git -c user.name="Sossio Giallaurito" -c user.email="sossiogll@gmail.com" add composer.json composer.lock
git -c user.name="Sossio Giallaurito" -c user.email="sossiogll@gmail.com" commit -q -m "Add the Filament media library plugin

Media fields of a content type are stored in the entry's media library
collections; the plugin binds Filament's upload field to them." 
V=vendor/filament
echo "### versions"; composer show 'filament/*' | head -20
echo "### Color.php"; grep -n "function " $V/support/src/Colors/Color.php | head -30
echo "### font"; ls $V/filament/src/FontProviders 2>/dev/null; ls $V/support/src/FontProviders 2>/dev/null; grep -rn "function font" $V/filament/src/Panel/Concerns/ | head
echo "### theme mode"; grep -rn "function defaultThemeMode\|function darkMode" $V/filament/src/Panel/Concerns/ | head
echo "### btn css"; grep -rn "fi-btn" $V/support/resources/css/components/button.css 2>/dev/null | head -5; ls $V/support/resources/css/components | head -30
echo "### Resource fns"; grep -n "function \(getNavigationItems\|getUrl\|getPages\|form\|table\|getEloquentQuery\|getNavigation\|getPluralModelLabel\|canCreate\)" $V/filament/src/Resources/Resource.php $V/filament/src/Resources/Concerns/*.php
echo "### pages"; grep -n "function " $V/filament/src/Resources/Pages/CreateRecord.php | head -60
echo "### edit"; grep -n "function " $V/filament/src/Resources/Pages/EditRecord.php | head -60
echo "### list"; grep -n "function " $V/filament/src/Resources/Pages/ListRecords.php | head -60
echo "### page route"; grep -rn "function route\|function getRouteName\|function registerRoutes" $V/filament/src/Pages/Page.php $V/filament/src/Resources/Pages/Page.php
echo "### spatie upload"; sed -n 1,80p $V/spatie-laravel-media-library-plugin/src/Forms/Components/SpatieMediaLibraryFileUpload.php
echo "### navitem"; grep -n "function " $V/filament/src/Navigation/NavigationItem.php | head -40
