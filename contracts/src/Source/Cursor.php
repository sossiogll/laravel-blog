<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Source;

/** An opaque position in a source, meaningful only to the source that made it. */
final readonly class Cursor
{
    public function __construct(public string $value)
    {
    }
}
