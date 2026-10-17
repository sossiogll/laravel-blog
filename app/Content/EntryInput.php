<?php

declare(strict_types=1);

namespace App\Content;

use Carbon\CarbonInterface;

/**
 * What a person gives for an entry, in the shape the contract's rules read: scalars for scalar fields, ids for
 * Reference fields, no value (null, not an empty string) for a field left blank. Images are not part of it, they
 * go straight to the media library.
 */
final readonly class EntryInput
{
    /**
     * @param string|null $slug null = from the title (new entry) or unchanged (existing one)
     * @param array<string, mixed> $fields the type's fields the source would own
     * @param array<string, mixed> $curated the type's curated fields
     */
    public function __construct(
        public string $title,
        public ?string $slug,
        public ?string $summary,
        public ?string $body,
        public string $language,
        public ?CarbonInterface $publishedAt,
        public array $fields,
        public array $curated,
    ) {
    }
}
