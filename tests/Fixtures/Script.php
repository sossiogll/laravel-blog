<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Gll\Hub\Contracts\Source\Cursor;
use Gll\Hub\Contracts\Source\ExternalEntry;
use Gll\Hub\Contracts\Source\SyncBatch;
use Throwable;

/**
 * What the fixture sources do on their next call, written by the test. Sources are
 * made by the container like real ones, so this is how a test reaches them.
 */
final class Script
{
    /** @var array<string, list<ExternalEntry>> listed by pull(), by source key */
    public array $entries = [];

    /** @var array<string, Throwable> thrown by pull() once the entries are yielded, by source key */
    public array $failures = [];

    /** @var list<SyncBatch|Throwable> answered (or thrown) by pullSince() in order; the last one repeats */
    public array $batches = [];

    /** @var array<string, ExternalEntry|null> answered by fetch(), by external id; a missing id is null */
    public array $fetchable = [];

    /** @var list<array{0: string, 1: mixed}> what was called: ["pull", settings], ["since", cursor value], ["fetch", id] */
    public array $calls = [];

    private int $batch = 0;

    /** @param list<SyncBatch|Throwable> $batches */
    public function useBatches(array $batches): void
    {
        $this->batches = $batches;
        $this->batch = 0;
    }

    public function nextBatch(?Cursor $cursor): SyncBatch
    {
        $this->calls[] = ['since', $cursor?->value];

        $next = $this->batches[min($this->batch++, count($this->batches) - 1)];

        return $next instanceof Throwable ? throw $next : $next;
    }

    /** @return list<string> */
    public function called(string $what): array
    {
        return array_values(array_map(fn (array $c): string => (string) $c[1], array_filter($this->calls, fn (array $c): bool => $c[0] === $what)));
    }
}
