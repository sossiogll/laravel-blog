<?php

declare(strict_types=1);

namespace App\Content;

use App\Models\Entry;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use RuntimeException;

/**
 * Stores an entry that comes from a source, keyed by (instance, external id).
 *
 * It is an upsert whose two arbiters are unique indexes, not a check: the read
 * below only picks the path, and an insert that loses a race on either index
 * falls back to the other path instead of failing. A native "insert or update"
 * is not used because entries have a second unique key, the slug, and MySQL
 * would then update whichever row collided first, which may be someone else's.
 *
 * Only the source's columns are written. `curated`, `author_id` and an existing
 * `slug` (a URL must not move when a title changes) are never touched.
 */
final class EntryWriter
{
    private const SLUG_ATTEMPTS = 5;

    public function __construct(private readonly SlugAllocator $slugs)
    {
    }

    /**
     * @param Closure(): array<string, mixed> $values the source-owned attributes; built only when something will be written
     * @return array{0: int, 1: WriteOutcome} the entry's id and what happened
     */
    public function save(int $instanceId, string $externalId, ?string $fingerprint, string $slugBase, Closure $values): array
    {
        $current = $this->current($instanceId, $externalId);

        if ($current === null) {
            $id = $this->insert($instanceId, $externalId, $slugBase, $values());
            if ($id !== null) {
                return [$id, WriteOutcome::Created];
            }
            // Another writer created it between our read and our insert; it is there now.
            $current = $this->current($instanceId, $externalId)
                ?? throw new RuntimeException("Entry {$externalId} of instance {$instanceId} vanished during its own insert");
        }

        if ($fingerprint !== null && $current->fingerprint === $fingerprint && $current->deleted_at === null) {
            return [$current->id, WriteOutcome::Unchanged];
        }

        Entry::withTrashed()->whereKey($current->id)->toBase()->update(
            Arr::except($this->columns($instanceId, $externalId, $values()), ['curated', 'created_at', 'source_instance_id', 'external_id']),
        );

        return [$current->id, WriteOutcome::Updated];
    }

    private function current(int $instanceId, string $externalId): ?Entry
    {
        return Entry::withTrashed()
            ->where('source_instance_id', $instanceId)
            ->where('external_id', $externalId)
            ->first(['id', 'fingerprint', 'deleted_at']);
    }

    /**
     * @param array<string, mixed> $values
     * @return int|null the new id, or null when the entry already exists
     */
    private function insert(int $instanceId, string $externalId, string $slugBase, array $values): ?int
    {
        $row = $this->columns($instanceId, $externalId, $values);

        for ($attempt = 0; $attempt < self::SLUG_ATTEMPTS; $attempt++) {
            $row['slug'] = $this->slugs->free($values['type'], $values['language'], $slugBase);

            try {
                return (int) Entry::query()->insertGetId($row);
            } catch (UniqueConstraintViolationException) {
                if ($this->current($instanceId, $externalId) !== null) {
                    return null;
                }
                // Otherwise the slug was claimed after we chose it: choose again.
            }
        }

        throw new RuntimeException("No free slug for \"{$slugBase}\" after " . self::SLUG_ATTEMPTS . ' attempts');
    }

    /**
     * The attributes as the database stores them (JSON encoded, dates in UTC), cast by the model itself.
     *
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function columns(int $instanceId, string $externalId, array $values): array
    {
        $entry = (new Entry())->forceFill($values + [
            'source_instance_id' => $instanceId,
            'external_id' => $externalId,
            'deleted_at' => null,
        ]);
        $entry->updateTimestamps();

        return $entry->getAttributes();
    }
}
