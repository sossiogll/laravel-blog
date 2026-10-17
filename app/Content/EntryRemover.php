<?php

declare(strict_types=1);

namespace App\Content;

use App\Models\Entry;

/** Soft-deletes entries a source no longer has. A source that lists them again brings them back. */
final class EntryRemover
{
    private const CHUNK = 500;

    /**
     * @param list<string> $externalIds
     * @return int how many entries were removed
     */
    public function remove(int $instanceId, array $externalIds): int
    {
        $removed = 0;
        foreach (array_chunk($externalIds, self::CHUNK) as $chunk) {
            $removed += Entry::where('source_instance_id', $instanceId)->whereIn('external_id', $chunk)->delete();
        }

        return $removed;
    }

    /**
     * Removes the instance's entries that are not in $seen: only valid after a complete listing.
     *
     * @param list<string> $seen external ids the source returned
     */
    public function removeUnseen(int $instanceId, array $seen): int
    {
        $live = Entry::where('source_instance_id', $instanceId)->pluck('external_id')->all();

        return $this->remove($instanceId, array_values(array_map('strval', array_diff($live, $seen))));
    }
}
