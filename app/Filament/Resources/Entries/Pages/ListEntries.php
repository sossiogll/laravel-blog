<?php

declare(strict_types=1);

namespace App\Filament\Resources\Entries\Pages;

use App\Filament\Resources\Entries\EntryResource;
use App\Packages\PackageRegistry;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class ListEntries extends ListRecords
{
    protected static string $resource = EntryResource::class;

    public function getTabs(): array
    {
        $tabs = [EntryResource::ALL => Tab::make('All')];
        foreach (app(PackageRegistry::class)->contentTypes() as $key => $type) {
            $tabs[$key] = Tab::make($type->pluralLabel)->modifyQueryUsing(static fn (Builder $query): Builder => $query->where('type', $key));
        }

        return $tabs;
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return EntryResource::ALL;
    }

    public function getTitle(): string|Htmlable
    {
        $type = app(PackageRegistry::class)->contentType((string) $this->activeTab);

        return $type?->pluralLabel ?? 'Entries';
    }

    protected function getHeaderActions(): array
    {
        $types = app(PackageRegistry::class)->contentTypes();
        $make = static fn (string $key, string $label): Action => Action::make("create_{$key}")
            ->label("New {$label}")
            ->icon(Heroicon::Plus)
            ->url(EntryResource::getUrl('create', ['type' => $key]));

        if (isset($types[$this->activeTab])) {
            return [$make($this->activeTab, $types[$this->activeTab]->label)];
        }

        $actions = [];
        foreach ($types as $key => $type) {
            $actions[] = $make($key, $type->label);
        }

        return $actions === [] ? [] : [ActionGroup::make($actions)->label('New entry')->icon(Heroicon::Plus)->button()];
    }
}
