<?php

declare(strict_types=1);

namespace App\Content;

use App\Models\Entry;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use RuntimeException;

/**
 * Picks the first free slug for a type and language: "title", then "title-2"...
 * The unique index (type, language, slug) stays the judge, since two writers can
 * pick the same slug at once; this only makes losing that race rare.
 */
class SlugAllocator
{
    private const ATTEMPTS = 5;

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

    /**
     * Stores with a free slug, choosing again when another writer claimed it first.
     *
     * @template T
     * @param Closure(string): T $store writes with the slug it is given and returns what to hand back
     * @param Closure(): bool|null $lostElsewhere asked when the store hit a unique index: true = the index was not
     *                                            the slug's, so choosing again would not help and null is returned
     * @return T|null
     */
    public function claim(string $type, string $language, string $base, Closure $store, ?Closure $lostElsewhere = null): mixed
    {
        for ($attempt = 0; $attempt < self::ATTEMPTS; $attempt++) {
            try {
                return $store($this->free($type, $language, $base));
            } catch (UniqueConstraintViolationException) {
                if ($lostElsewhere !== null && $lostElsewhere()) {
                    return null;
                }
            }
        }

        throw new RuntimeException("No free slug for \"{$base}\" after " . self::ATTEMPTS . ' attempts');
    }
}
