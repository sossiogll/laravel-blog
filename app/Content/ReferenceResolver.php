<?php

declare(strict_types=1);

namespace App\Content;

use App\Models\Entry;
use App\Models\EntryReference;
use Illuminate\Support\Collection;

/**
 * Points unresolved references at the entries they name. Run after every sync:
 * a reference to an entry that arrives later in the run, or from another source,
 * is found then. What still has no target stays as it is and is tried again next time.
 *
 * By external id, the target is looked up in the referring entry's own instance;
 * by type and slug, anywhere, preferring the referring entry's language.
 */
final class ReferenceResolver
{
    private const CHUNK = 200;

    /** @return int how many references now have a target */
    public function resolve(): int
    {
        $resolved = 0;

        EntryReference::unresolved()
            ->with('entry:id,source_instance_id,language')
            ->chunkById(self::CHUNK, function (Collection $references) use (&$resolved): void {
                $byExternalId = $this->byExternalId($references);
                $bySlug = $this->bySlug($references);

                foreach ($references as $reference) {
                    $target = $this->target($reference, $byExternalId, $bySlug);
                    if ($target !== null && $target !== $reference->entry_id) {
                        $reference->forceFill(['target_entry_id' => $target])->save();
                        $resolved++;
                    }
                }
            });

        return $resolved;
    }

    /**
     * @param array<int, array<string, int>> $byExternalId
     * @param array<string, array<string, array<int, string>>> $bySlug
     */
    private function target(EntryReference $reference, array $byExternalId, array $bySlug): ?int
    {
        $from = $reference->entry;
        if ($from === null) {
            return null;
        }

        if ($reference->external_id !== null && $from->source_instance_id !== null) {
            $found = $byExternalId[$from->source_instance_id][$reference->external_id] ?? null;
            if ($found !== null) {
                return $found;
            }
        }
        if ($reference->type !== null && $reference->slug !== null) {
            return $this->preferring($bySlug[$reference->type][$reference->slug] ?? [], $from->language);
        }

        return null;
    }

    /**
     * @param Collection<int, EntryReference> $references
     * @return array<int, array<string, int>> entry id by external id, by instance id
     */
    private function byExternalId(Collection $references): array
    {
        $wanted = [];
        foreach ($references as $reference) {
            if ($reference->external_id !== null && $reference->entry?->source_instance_id !== null) {
                $wanted[$reference->entry->source_instance_id][] = $reference->external_id;
            }
        }

        $found = [];
        foreach ($wanted as $instanceId => $externalIds) {
            $found[$instanceId] = Entry::where('source_instance_id', $instanceId)
                ->whereIn('external_id', array_unique($externalIds))
                ->pluck('id', 'external_id')
                ->all();
        }

        return $found;
    }

    /**
     * @param Collection<int, EntryReference> $references
     * @return array<string, array<string, array<int, string>>> languages by entry id, by slug, by type
     */
    private function bySlug(Collection $references): array
    {
        $wanted = [];
        foreach ($references as $reference) {
            if ($reference->type !== null && $reference->slug !== null) {
                $wanted[$reference->type][] = $reference->slug;
            }
        }

        $found = [];
        foreach ($wanted as $type => $slugs) {
            foreach (Entry::where('type', $type)->whereIn('slug', array_unique($slugs))->get(['id', 'slug', 'language']) as $entry) {
                $found[$type][$entry->slug][$entry->id] = $entry->language;
            }
        }

        return $found;
    }

    /** @param array<int, string> $languagesById */
    private function preferring(array $languagesById, string $language): ?int
    {
        $same = array_search($language, $languagesById, true);

        return $same !== false ? $same : array_key_first($languagesById);
    }
}
