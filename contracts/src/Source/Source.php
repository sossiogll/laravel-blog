<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Source;

use Gll\Hub\Contracts\Package\Context;

/**
 * Brings content in from an outside service.
 *
 * A source returns DATA and decides nothing: the core matches entries by
 * (instance, externalId), creates or updates them, keeps curated fields, applies
 * locks, downloads media, links references and removes what the source says is gone.
 *
 * Errors: throw the exceptions of Gll\Hub\Contracts\Exceptions; the core retries,
 * waits or marks the instance as failing. Any other exception is treated as a bug
 * of the source and the run is failed.
 */
interface Source
{
    public static function definition(): SourceDefinition;

    /**
     * What the instance currently has: everything, or only the latest when
     * SourceDefinition::$listsEverything is false. Only in the first case does the
     * core treat an entry missing here as removed.
     *
     * @return iterable<ExternalEntry>
     */
    public function pull(Context $context): iterable;
}
