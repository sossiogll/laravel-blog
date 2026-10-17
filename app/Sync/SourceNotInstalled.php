<?php

declare(strict_types=1);

namespace App\Sync;

use RuntimeException;

/** An instance whose package is gone or was rejected: only the admin can fix that. */
final class SourceNotInstalled extends RuntimeException
{
    public static function for(string $package, string $key): self
    {
        return new self("The source {$package}/{$key} is not installed, or its package was rejected (see gllhub:packages).");
    }
}
