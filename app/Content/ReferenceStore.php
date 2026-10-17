<?php

declare(strict_types=1);

namespace App\Content;

use App\Models\EntryReference;
use Gll\Hub\Contracts\Source\ExternalRef;
use Illuminate\Support\Facades\DB;

/** Keeps what a source said an entry refers to; ReferenceResolver turns it into links between entries. */
final class ReferenceStore
{
    /** @param list<ExternalRef> $references the complete set: what is not in it is dropped */
    public function replace(int $entryId, array $references): void
    {
        $position = [];
        $rows = [];
        foreach ($references as $ref) {
            $rows[] = [
                'entry_id' => $entryId,
                'field' => $ref->field,
                'position' => $position[$ref->field] = ($position[$ref->field] ?? -1) + 1,
                'external_id' => $ref->externalId,
                'type' => $ref->type,
                'slug' => $ref->slug,
            ];
        }

        DB::transaction(function () use ($entryId, $rows): void {
            EntryReference::where('entry_id', $entryId)->delete();
            if ($rows !== []) {
                EntryReference::insert($rows);
            }
        });
    }
}
