<?php

declare(strict_types=1);

namespace App\Webhooks;

use App\Packages\Provided;

/** Where an outside service must send a webhook: the public address the core was told it has, not the one of the current request. */
final class WebhookUrl
{
    public static function of(Provided $webhook): string
    {
        return rtrim((string) config('app.url'), '/') . "/webhooks/{$webhook->package}/{$webhook->key()}";
    }
}
