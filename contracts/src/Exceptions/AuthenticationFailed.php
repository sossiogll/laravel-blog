<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Exceptions;

/** The person cancelled or the outside service refused the sign-in. The core shows a generic message. */
final class AuthenticationFailed extends GllHubException
{
}
