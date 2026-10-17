#!/usr/bin/env bash
set -euo pipefail
exec </dev/null
cp .env.example .env
composer install --no-interaction --prefer-dist --no-progress >/dev/null 2>&1
php artisan key:generate >/dev/null
set +e
php artisan test 2>&1 | tail -60
(cd contracts && composer install -q --no-interaction >/dev/null 2>&1 && vendor/bin/phpunit 2>&1 | tail -8)
V=vendor/filament
echo "### Page content"; grep -n "function content\|string \$view" $V/filament/src/Pages/Page.php
echo "### markAsRequired"; grep -rn "function markAsRequired" $V/*/src | head -3
echo "### select"; grep -n "public function \(getOptionLabel\|getOptionLabels\|getSearchResultsUsing\)" $V/forms/src/Components/Select.php
echo "### tabs"; grep -rn "activeTab" $V/filament/src/Resources/Pages/ListRecords/Concerns/*.php | head -8
echo "### Heroicon"; grep -c "OutlinedDocumentText\|OutlinedCircleStack\|OutlinedPuzzlePiece\|OutlinedArrowPath\|OutlinedPencilSquare" $V/support/src/Icons/Heroicon.php
echo "### makeuser"; grep -n "name\|email\|password" $V/filament/src/Commands/MakeUserCommand.php | head -20
echo "### testing"; grep -rhn "function \(assert\|fill\|call\)[A-Za-z]*" $V/schemas/src/Testing $V/forms/src/Testing $V/tables/src/Testing $V/actions/src/Testing 2>/dev/null | sed 's/^\s*//' | cut -c1-120 | head -70
echo "### Livewire comp"; grep -n "function make\|function key" $V/schemas/src/Components/Livewire.php
echo "### color"; php -r 'require "vendor/autoload.php"; $p=Filament\Support\Colors\Color::generateV3Palette("#d4ff3a"); print_r($p); $m=Filament\Support\View\Components\ColorMaps\ButtonComponentColorMap::make($p)->lightBackground(600,500)->darkBackground(500,400)->get(); print_r($m); foreach(["text","dark:text"] as $k){} '
