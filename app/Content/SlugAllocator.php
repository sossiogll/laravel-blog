<?php

declare(strict_types=1);

namespace App\Content;

use App\Models\Entry;

/**
 * Picks the first free slug for a type and language: "title", then "title-2"...
 * The unique index (type, language, slug) stays the judge, since two writers can
 * pick the same slug at once; this only makes losing that race rare.
 */
class SlugAllocator
{
    public function free(string $type, string $language, string $base): string
    {
        // Trashed entries keep their slug: the index still counts them.
        $taken = Entry::withTrashed()
            ->where('type', $type)
            ->where('language', $language)
            ->where(fn ($q) => $q->where('slug', $base)->orWhere('slug', 'like', $base . '-%'))
            ->pluck('slug')
            ->flip();

        $slug = $base;
        for ($n = 2; $taken->has($slug); $n++) {
            $slug = "{$base}-{$n}";
        }

        return $slug;
    }
}
