<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Exceptions;

use Throwable;

/** The outside service asked to slow down. The core waits at least $retryAfter seconds. */
final class RateLimited extends GllHubException
{
    public function __construct(
        public readonly int $retryAfter = 60,
        string $message = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
