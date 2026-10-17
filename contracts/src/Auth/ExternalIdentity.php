<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Auth;

/** Who the outside service says the person is. */
final readonly class ExternalIdentity
{
    public function __construct(
        public string $id,
        public ?string $email = null,
        public bool $emailVerified = false,
        public ?string $name = null,
        public ?string $username = null,
        public ?string $avatarUrl = null,
        public ?string $profileUrl = null,
    ) {
    }
}
