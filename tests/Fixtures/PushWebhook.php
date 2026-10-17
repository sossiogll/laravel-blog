<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Gll\Hub\Contracts\Package\Context;
use Gll\Hub\Contracts\Package\Credential;
use Gll\Hub\Contracts\Webhook\IncomingWebhook;
use Gll\Hub\Contracts\Webhook\SyncRequest;
use Gll\Hub\Contracts\Webhook\WebhookDefinition;
use Gll\Hub\Contracts\Webhook\WebhookReceiver;

/** Body: {"requests": [{"source": "catalog", "instance": {"shelf": "a"}, "ids": ["1"]}]} */
final class PushWebhook implements WebhookReceiver
{
    public static function definition(): WebhookDefinition
    {
        return new WebhookDefinition(
            key: 'push',
            label: 'Push',
            refreshes: ['catalog', 'latest', 'changes'],
            credentials: [new Credential('secret', 'Shared secret')],
        );
    }

    public function verify(Context $context, IncomingWebhook $webhook): bool
    {
        $secret = $context->credential('secret');

        return $secret !== null && hash_equals($secret, (string) $webhook->header('x-secret'));
    }

    public function handle(Context $context, IncomingWebhook $webhook): array
    {
        return array_map(
            static fn (array $r): SyncRequest => new SyncRequest($r['source'], $r['instance'] ?? [], $r['ids'] ?? []),
            $webhook->json()['requests'] ?? [],
        );
    }
}
