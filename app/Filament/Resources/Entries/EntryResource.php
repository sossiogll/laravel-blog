<?php

declare(strict_types=1);

namespace App\Filament\Resources\Entries;

use App\Filament\Resources\Entries\Pages\CreateEntry;
use App\Filament\Resources\Entries\Pages\EditEntry;
use App\Filament\Resources\Entries\Pages\ListEntries;
use App\Models\Entry;
use App\Models\SourceInstance;
use App\Packages\PackageRegistry;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

use function Filament\Support\original_request;

/**
 * Every entry of every content type, in one resource. What a type is called and what fields it has come from the
 * registry, so a package adds a type to the admin by being installed.
 */
class EntryResource extends Resource
{
    public const ALL = '_all';

    protected static ?string $model = Entry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $modelLabel = 'entry';

    protected static ?string $pluralModelLabel = 'entries';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        // The trashed filter decides what is shown.
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    /** One item per content type, pointing at the list scoped to it. */
    public static function getNavigationItems(): array
    {
        $items = [];
        $sort = 0;
        foreach (app(PackageRegistry::class)->contentTypes() as $key => $type) {
            $items[] = NavigationItem::make($type->pluralLabel)
                ->key(static::class . ':' . $key)
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->sort($sort++)
                ->isActiveWhen(static fn (): bool => original_request()->routeIs(static::getRouteBaseName() . '.*') && original_request()->query('tab') === $key)
                ->url(static::getUrl('index', ['tab' => $key]));
        }

        return $items;
    }

    public static function form(Schema $schema): Schema
    {
        // The fields depend on the type of the entry, so each page builds its form (see EntryForm).
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->limit(60),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(static fn (string $state): string => app(PackageRegistry::class)->contentType($state)?->label ?? $state)
                    ->toggleable(),
                TextColumn::make('language')->badge()->sortable(),
                TextColumn::make('sourceInstance.label')->label('Origin')->placeholder('Written by hand'),
                TextColumn::make('published_at')->dateTime()->sortable()->placeholder('Not published'),
                TextColumn::make('synced_at')->dateTime()->sortable()->placeholder('Never'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('origin')
                    ->options(static fn (): array => ['hand' => 'Written by hand'] + SourceInstance::query()->orderBy('label')->pluck('label', 'id')->all())
                    ->query(static function (Builder $query, array $data): Builder {
                        $origin = $data['value'] ?? null;

                        return match (true) {
                            blank($origin) => $query,
                            $origin === 'hand' => $query->whereNull('source_instance_id'),
                            default => $query->where('source_instance_id', $origin),
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                // A synced entry deleted here would be brought back by the next sync, so only the person's can be removed.
                DeleteAction::make()->visible(static fn (Entry $record): bool => $record->isHandWritten() && ! $record->trashed()),
                RestoreAction::make(),
                ForceDeleteAction::make()->visible(static fn (Entry $record): bool => $record->isHandWritten() && $record->trashed()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEntries::route('/'),
            'create' => CreateEntry::route('/create'),
            'edit' => EditEntry::route('/{record}/edit'),
        ];
    }
}
