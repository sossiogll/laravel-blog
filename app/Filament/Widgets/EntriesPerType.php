<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Entry;
use App\Packages\PackageRegistry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EntriesPerType extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Entries';

    protected function getStats(): array
    {
        $counts = Entry::query()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');

        $stats = [];
        foreach (app(PackageRegistry::class)->contentTypes() as $key => $type) {
            $stats[] = Stat::make($type->pluralLabel, (int) ($counts[$key] ?? 0));
        }

        return $stats;
    }
}
