<?php

declare(strict_types=1);

namespace App\Content\Rules;

use Illuminate\Support\Str;

final class Slug
{
    /** Leaves room under the column for a "-12" suffix. */
    private const MAX_LENGTH = 150;

    /** The URL-safe form of a slug or a title; never empty. */
    public static function from(string $text): string
    {
        $slug = trim(substr(Str::slug($text), 0, self::MAX_LENGTH), '-');

        return $slug === '' ? 'entry' : $slug;
    }
}
