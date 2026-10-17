<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Source;

use Gll\Hub\Contracts\Package\Context;

/**
 * Capability::Incremental. The source resumes from where the last run stopped.
 * The core stores the cursor of the last batch and passes it back; a null cursor
 * means "from the beginning". Missing entries are NOT removed in this mode: the
 * source names what is gone in SyncBatch::$removed.
 */
interface SyncsIncrementally
{
    public function pullSince(Context $context, ?Cursor $cursor): SyncBatch;
}
