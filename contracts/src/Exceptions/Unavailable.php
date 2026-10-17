<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Exceptions;

/** The outside service is down or timed out. The core retries later with backoff. */
final class Unavailable extends GllHubException
{
}
