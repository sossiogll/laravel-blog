<?php

declare(strict_types=1);

namespace App\Sync;

use App\Content\WriteOutcome;

/** What one run did, for the logs and for the command that started it. */
final class SyncReport
{
    public int $created = 0;
    public int $updated = 0;
    public int $unchanged = 0;
    public int $removed = 0;

    /** @var array<string, list<string>> violations by external id of the entries that were skipped */
    public array $invalid = [];

    public function wrote(WriteOutcome $outcome): void
    {
        match ($outcome) {
            WriteOutcome::Created => $this->created++,
            WriteOutcome::Updated => $this->updated++,
            WriteOutcome::Unchanged => $this->unchanged++,
        };
    }

    /** @param list<string> $violations */
    public function skipped(string $externalId, array $violations): void
    {
        $this->invalid[$externalId] = $violations;
    }

    public function summary(): string
    {
        return sprintf(
            '%d created, %d updated, %d unchanged, %d removed, %d skipped',
            $this->created,
            $this->updated,
            $this->unchanged,
            $this->removed,
            count($this->invalid),
        );
    }
}
