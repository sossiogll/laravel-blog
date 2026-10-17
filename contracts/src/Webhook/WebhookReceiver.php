<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Webhook;

use Gll\Hub\Contracts\Package\Context;

/**
 * Turns a call from an outside service into sync requests. It does not sync
 * anything itself: it only says what changed, and the core decides when to pull.
 */
interface WebhookReceiver
{
    public static function definition(): WebhookDefinition;

    /** False = the core answers 401 and calls nothing else. */
    public function verify(Context $context, IncomingWebhook $webhook): bool;

    /** @return list<SyncRequest> empty = nothing to do (a ping, an event it ignores) */
    public function handle(Context $context, IncomingWebhook $webhook): array;
}
