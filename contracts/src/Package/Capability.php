<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Package;

use Gll\Hub\Contracts\Source\FetchesSingleEntry;
use Gll\Hub\Contracts\Source\SyncsIncrementally;

/**
 * Optional abilities, declared in the manifest AND implemented as an interface.
 *
 * The core reads the manifest (it never guesses from a class); the conformance check
 * fails when the two disagree. A new optional ability = a new case here + a new
 * interface, never a new method on an existing interface.
 */
enum Capability: string
{
    /** The source can resume from a cursor instead of reading everything. */
    case Incremental = 'incremental';

    /** The source can fetch one entry by its external id (used after a webhook). */
    case SingleFetch = 'single_fetch';

    /** The interface a class must implement to have this capability. */
    public function contract(): string
    {
        return match ($this) {
            self::Incremental => SyncsIncrementally::class,
            self::SingleFetch => FetchesSingleEntry::class,
        };
    }
}
