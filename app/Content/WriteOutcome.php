<?php

declare(strict_types=1);

namespace App\Content;

/** What storing an entry from a source did. */
enum WriteOutcome: string
{
    case Created = 'created';
    case Updated = 'updated';
    /** Same fingerprint as the stored copy: nothing was written. */
    case Unchanged = 'unchanged';
}
