<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Auth;

use Gll\Hub\Contracts\Package\Context;

/**
 * Signs a person in through an outside account. The provider only talks to its
 * service; the core makes and checks the state, decides whether a new account may
 * be created, links the identity to a user and opens the session.
 */
interface AuthProvider
{
    public static function definition(): AuthDefinition;

    /** Where to send the browser. $state must be passed through unchanged. */
    public function redirectUrl(Context $context, string $callbackUrl, string $state): string;

    /**
     * Called on the callback with its query string, after the core has checked the state.
     *
     * @param array<string, string> $query
     */
    public function resolve(Context $context, string $callbackUrl, array $query): ExternalIdentity;
}
