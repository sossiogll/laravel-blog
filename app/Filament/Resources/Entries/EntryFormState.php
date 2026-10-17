<?php

declare(strict_types=1);

namespace App\Filament\Resources\Entries;

use App\Content\EntryInput;
use App\Content\InvalidEntry;
use App\Filament\Fields\FieldFactory;
use App\Filament\Fields\FormValues;
use App\Models\Entry;
use Filament\Notifications\Notification;
use Gll\Hub\Contracts\Content\ContentTypeDefinition;
use Gll\Hub\Contracts\Content\Field;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/** Between the entry form and the core's EntryEditor: what the form holds in, and the editor's refusals out. */
final class EntryFormState
{
    /** @param array<string, mixed> $data */
    public static function input(?ContentTypeDefinition $type, array $data, ?Entry $current): EntryInput
    {
        $fields = $type?->fields ?? [];
        $published = $data['published_at'] ?? null;

        return new EntryInput(
            title: (string) ($data['title'] ?? $current?->title ?? ''),
            slug: self::text($data['slug'] ?? null),
            summary: self::text($data['summary'] ?? null),
            body: self::text($data['body'] ?? null),
            language: (string) ($data['language'] ?? $current?->language ?? ''),
            publishedAt: filled($published) ? Carbon::parse($published, 'UTC') : null,
            fields: FormValues::fields(array_values(array_filter($fields, static fn (Field $f): bool => ! $f->curated)), $data['fields'] ?? []),
            curated: FormValues::fields(array_values(array_filter($fields, static fn (Field $f): bool => $f->curated)), $data['curated'] ?? []),
        );
    }

    /**
     * The editor's violations as errors under the fields they name. The rest cannot be placed on a field, so
     * they go to a notification: nothing is refused silently.
     */
    public static function refusal(InvalidEntry $refused, ?ContentTypeDefinition $type): ValidationException
    {
        $errors = [];
        $unplaced = [];
        foreach ($refused->violations as $violation) {
            $path = self::path($violation, $type);
            if ($path === null) {
                $unplaced[] = $violation;
            } else {
                $errors[$path][] = ucfirst($violation);
            }
        }

        if ($unplaced !== []) {
            Notification::make()->danger()->title('The entry was not saved')->body(implode("\n", $unplaced))->send();
        }

        return ValidationException::withMessages($errors);
    }

    private static function path(string $violation, ?ContentTypeDefinition $type): ?string
    {
        // The contract phrases a problem after the field it is about: `field "x" ...`, `item 2 of "x": ...`.
        if (preg_match('/^item \d+ of "([^"\[]+)/', $violation, $m) === 1 || preg_match('/field "([^"\[]+)/', $violation, $m) === 1) {
            $field = $type?->field($m[1]);

            return $field === null ? null : 'data.' . FieldFactory::path($field);
        }

        return match (true) {
            str_contains($violation, 'title') => 'data.title',
            str_starts_with($violation, 'language') => 'data.language',
            default => null,
        };
    }

    private static function text(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? $value : null;
    }
}
