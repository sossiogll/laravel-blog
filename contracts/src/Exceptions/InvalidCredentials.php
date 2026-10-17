<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Exceptions;

/** A credential is missing, wrong or expired. The core stops the instance and asks the admin. */
final class InvalidCredentials extends GllHubException
{
}
