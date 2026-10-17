<?php

declare(strict_types=1);

namespace App\Content;

use RuntimeException;

/** What a person wrote does not fit the content type; the violations are phrased by the same rules sources are held to. */
final class InvalidEntry extends RuntimeException
{
    /** @param list<string> $violations */
    public function __construct(public readonly array $violations)
    {
        parent::__construct(implode("\n", $violations));
    }
}
