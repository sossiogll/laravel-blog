<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Support;

/** The one way a package builds ExternalEntry::$fingerprint from what makes an entry change. */
final class Fingerprint
{
    public static function of(?string ...$parts): string
    {
        return hash('sha256', implode("\x1f", array_map(static fn (?string $p): string => $p ?? '', $parts)));
    }
}
