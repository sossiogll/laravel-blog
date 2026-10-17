#!/usr/bin/env bash
set -euo pipefail
exec </dev/null
cp .env.example .env
composer install --no-interaction --prefer-dist --no-progress >/dev/null 2>&1
set +e
V=vendor/filament
echo "### ButtonComponentColorMap"; cat $V/support/src/View/Components/ColorMaps/ButtonComponentColorMap.php | head -120
echo "### ComponentColorMap"; sed -n 1,140p $V/support/src/View/Components/ColorMaps/ComponentColorMap.php
echo "### list makeTable rest"; sed -n 135,215p $V/filament/src/Resources/Pages/ListRecords.php
echo "### tab"; find $V -name Tab.php | head; find $V -name ThemeMode.php | head; find $V -name Heroicon.php | head -2
echo "### list tabs"; grep -n "activeTab\|function getTabs\|function getDefaultActiveTab\|Url" $V/filament/src/Resources/Pages/ListRecords/Concerns/HasTabs.php
echo "### make user"; grep -n "signature\|--" $V/filament/src/Commands/MakeUserCommand.php | head
echo "### Panel navgroups"; grep -n "function navigationGroups" -B3 -A20 $V/filament/src/Panel/Concerns/HasNavigation.php | head -50
echo "### Resource HasNavigation getNavigationItems"; sed -n 55,100p $V/filament/src/Resources/Resource/Concerns/HasNavigation.php
echo "### table filters url"; grep -rn "Url(" $V/tables/src/Concerns/*.php | head
echo "### trashedfilter"; sed -n 1,80p $V/tables/src/Filters/TrashedFilter.php
echo "### Resource getUrl"; sed -n 90,150p $V/filament/src/Resources/Resource/Concerns/HasPages.php 2>/dev/null; ls $V/filament/src/Resources/Resource/Concerns
echo "### repeater simple"; grep -n "function simple\|function itemLabel\|function addable\|function reorderable" $V/forms/src/Components/Repeater.php
echo "### Authenticate"; cat $V/filament/src/Http/Middleware/Authenticate.php | sed -n 1,80p
echo "### sticky stats"; grep -n "function " $V/widgets/src/StatsOverviewWidget.php | head -30
