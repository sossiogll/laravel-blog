<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Webhook;

use Gll\Hub\Contracts\Package\Credential;

/**
 * A webhook endpoint a package exposes. The core serves it at
 * POST {publicUrl}/webhooks/{package}/{key} and passes the raw request in.
 */
final readonly class WebhookDefinition
{
    /**
     * @param list<string> $refreshes the source keys (in the same package) it can ask to sync again
     * @param list<Credential> $credentials e.g. the shared secret used to sign requests
     */
    public function __construct(
        public string $key,
        public string $label,
        public array $refreshes,
        public array $credentials = [],
        public string $description = '',
    ) {
    }
}
