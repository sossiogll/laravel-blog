<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Exceptions;

use RuntimeException;

/**
 * The errors a package may throw on purpose. The core reads the class, never the
 * message, to decide what to do. Anything else a package throws is a bug of the package.
 */
abstract class GllHubException extends RuntimeException
{
}
