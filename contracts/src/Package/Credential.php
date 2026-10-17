<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Package;

/**
 * A secret a package needs (a token, a webhook secret, an OAuth client secret).
 * The core stores it encrypted, never shows it back in the admin and hands it to the
 * package only through its context, at call time.
 */
final readonly class Credential
{
    public function __construct(
        public string $key,
        public string $label,
        public bool $required = true,
        public string $help = '',
    ) {
    }
}
