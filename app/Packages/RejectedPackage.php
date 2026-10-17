<?php

declare(strict_types=1);

namespace App\Packages;

/** A package the core found but will not run, and why. It never stops the app. */
final readonly class RejectedPackage
{
    /** @param list<string> $reasons */
    public function __construct(
        public string $name,
        public array $reasons,
    ) {
    }
}
