<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Exceptions;

use Throwable;

/** A setting has a value the package cannot use. The core shows it next to that setting. */
final class InvalidConfiguration extends GllHubException
{
    public function __construct(
        public readonly string $setting,
        string $message = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
