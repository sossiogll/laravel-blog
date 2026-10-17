<?php

declare(strict_types=1);

namespace App\Filament\Resources\Entries;

use App\Content\Rules\Slug;
use App\Filament\Fields\FieldFactory;
use App\Models\Entry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Gll\Hub\Contracts\Content\ContentTypeDefinition;
use Gll\Hub\Contracts\Content\Field;

/**
 * The form of an entry: the core's own fields, then the fields its type declares. What a person may edit follows
 * who owns it: everything on a hand-written entry, only the curated fields on a synced one.
 */
final class EntryForm
{
    public static function configure(Schema $schema, ?ContentTypeDefinition $type, ?Entry $entry): Schema
    {
        $creating = $entry === null;
        // An entry whose type is no longer installed has no fields to edit, so it is shown and left alone.
        $locked = $entry !== null && (! $entry->isHandWritten() || $type === null);
        $fields = $type?->fields ?? [];

        $sections = [self::core($creating, $locked, $entry)];
        if ($locked) {
            $sections[] = self::fields('From the source', 'The source owns these; the next sync overwrites any change.', array_filter($fields, static fn (Field $f): bool => ! $f->curated), true);
            $sections[] = self::fields('Curated', 'Yours: a sync never touches these.', array_filter($fields, static fn (Field $f): bool => $f->curated), false);
        } else {
            $sections[] = self::fields($type?->label ?? 'Fields', null, $fields, false);
        }

        return $schema->components(array_values(array_filter($sections)));
    }

    private static function core(bool $creating, bool $locked, ?Entry $entry): Section
    {
        $heading = $locked
            ? 'Synced from ' . ($entry?->sourceInstance?->label ?? 'a source')
            : 'Entry';

        return Section::make($heading)
            ->description($locked ? 'These values belong to the source and are shown as they were last synced.' : null)
            ->columns(2)
            ->schema([
                TextInput::make('title')
                    ->markAsRequired()
                    ->extraInputAttributes(['maxlength' => 500])
                    ->live(onBlur: true)
                    ->afterStateUpdated(static function (Get $get, Set $set, ?string $old, ?string $state) use ($creating): void {
                        // Follow the title until the person writes a slug of their own.
                        $current = (string) $get('slug');
                        if ($creating && ($current === '' || $current === self::slugOf($old))) {
                            $set('slug', self::slugOf($state));
                        }
                    })
                    ->columnSpanFull(),
                TextInput::make('slug')
                    ->helperText($creating ? 'Built from the title; change it to choose your own.' : 'The address of the entry. Empty keeps the current one.'),
                TextInput::make('language')
                    ->markAsRequired()
                    ->default(config('app.locale'))
                    ->helperText('A language tag such as en or pt-BR.'),
                DateTimePicker::make('published_at')
                    ->label('Published at')
                    ->timezone('UTC')
                    ->seconds(false)
                    ->helperText('UTC. Empty = not published.'),
                SpatieMediaLibraryFileUpload::make('media.thumbnail')
                    ->label('Thumbnail')
                    ->collection(Entry::THUMBNAIL)
                    ->image(),
                Textarea::make('summary')->rows(3)->columnSpanFull(),
                MarkdownEditor::make('body')->columnSpanFull(),
            ])
            ->disabled($locked)
            ->dehydrated(! $locked);
    }

    /**
     * @param array<array-key, Field> $fields
     */
    private static function fields(string $heading, ?string $description, array $fields, bool $readOnly): ?Section
    {
        if ($fields === []) {
            return null;
        }

        return Section::make($heading)
            ->description($description)
            ->columns(2)
            ->schema(array_map(static fn (Field $field) => FieldFactory::field($field, $readOnly), array_values($fields)));
    }

    private static function slugOf(?string $text): string
    {
        return trim((string) $text) === '' ? '' : Slug::from((string) $text);
    }
}
