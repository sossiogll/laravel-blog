<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Source;

use Gll\Hub\Contracts\Package\Context;

/**
 * Capability::SingleFetch. The source can refresh one entry, typically after a
 * webhook named it. Null means the entry no longer exists: the core removes it.
 */
interface FetchesSingleEntry
{
    public function fetch(Context $context, string $externalId): ?ExternalEntry;
}
