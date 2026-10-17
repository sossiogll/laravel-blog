<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Packages\PackageRegistry;
use App\Webhooks\WebhookIntake;
use Gll\Hub\Contracts\Webhook\IncomingWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WebhookController
{
    public function __invoke(Request $request, PackageRegistry $registry, WebhookIntake $intake, string $package, string $key): JsonResponse
    {
        $webhook = $registry->webhook($package, $key);
        if ($webhook === null) {
            return response()->json(['message' => 'Unknown webhook.'], 404);
        }

        $headers = [];
        foreach ($request->headers->all() as $name => $values) {
            $headers[strtolower((string) $name)] = implode(', ', $values);
        }

        $queued = $intake->receive($webhook, new IncomingWebhook(
            $request->getContent(),
            $headers,
            array_filter($request->query->all(), 'is_string'),
        ));

        return $queued === null
            ? response()->json(['message' => 'The call could not be verified.'], 401)
            : response()->json(['queued' => $queued], 202);
    }
}
