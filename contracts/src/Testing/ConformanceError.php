<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Testing;

use AssertionError;

/** Every violation found, in one error, so a package fixes them all at once. */
final class ConformanceError extends AssertionError
{
    /** @param list<string> $violations */
    public function __construct(public readonly array $violations)
    {
        parent::__construct("The package does not conform to gllhub/contracts:\n - " . implode("\n - ", $violations));
    }
}
