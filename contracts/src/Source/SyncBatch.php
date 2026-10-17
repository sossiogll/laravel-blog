<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Source;

/** One step of an incremental sync. */
final readonly class SyncBatch
{
    /**
     * @param list<ExternalEntry> $entries new or changed entries
     * @param list<string> $removed external ids that no longer exist
     * @param Cursor|null $cursor where to resume next time
     * @param bool $hasMore true when the core should call again right away
     */
    public function __construct(
        public array $entries,
        public array $removed = [],
        public ?Cursor $cursor = null,
        public bool $hasMore = false,
    ) {
    }
}
