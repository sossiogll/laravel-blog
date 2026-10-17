<?php

declare(strict_types=1);

namespace App\Content\Rules;

use Gll\Hub\Contracts\Content\ContentTypeDefinition;
use Gll\Hub\Contracts\Content\EntryRules;
use Gll\Hub\Contracts\Content\FieldRules;

/**
 * Checks what a person writes against the fields of the content type: the values
 * they curate on a synced entry, or a whole entry written by hand. The rules of a
 * single value are the contract's (FieldRules), the same ones sources are held to.
 */
final class EntryValidator
{
    /**
     * @param array<string, mixed> $values
     * @return list<string>
     */
    public static function curated(ContentTypeDefinition $type, array $values): array
    {
        $v = [];
        foreach ($values as $key => $value) {
            $field = $type->field((string) $key);
            if ($field === null) {
                $v[] = "field \"{$key}\" is not in type {$type->key}";
            } elseif (! $field->curated) {
                $v[] = "field \"{$key}\" belongs to the source, it cannot be curated";
            } else {
                array_push($v, ...FieldRules::violations($field, $value));
            }
        }

        return $v;
    }

    /**
     * The columns every entry has whatever its type, held to the limits a source's entries are held to.
     *
     * @return list<string>
     */
    public static function columns(string $title, string $language): array
    {
        $v = [];
        if (mb_strlen($title) > EntryRules::MAX_TITLE) {
            $v[] = 'the title is longer than ' . EntryRules::MAX_TITLE . ' characters';
        }
        if (! EntryRules::isLanguage($language)) {
            $v[] = "language \"{$language}\" is not a language tag such as \"en\" or \"pt-BR\"";
        }

        return $v;
    }

    /**
     * An entry with no source owns all its fields: those flagged curated go in
     * $curated, the others in $fields.
     *
     * @param array<string, mixed> $fields
     * @param array<string, mixed> $curated
     * @return list<string>
     */
    public static function handWritten(ContentTypeDefinition $type, string $title, array $fields, array $curated): array
    {
        $v = trim($title) === '' ? ['the title is empty'] : [];

        foreach ($fields as $key => $value) {
            $field = $type->field((string) $key);
            if ($field === null) {
                $v[] = "field \"{$key}\" is not in type {$type->key}";
            } elseif ($field->curated) {
                $v[] = "field \"{$key}\" is curated, put it with the curated values";
            } else {
                array_push($v, ...FieldRules::violations($field, $value));
            }
        }
        array_push($v, ...self::curated($type, $curated));
        array_push($v, ...FieldRules::missing($type->fields, $fields + $curated));

        return $v;
    }
}
