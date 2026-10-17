<?php

declare(strict_types=1);

namespace App\Webhooks;

use App\Models\SourceInstance;
use App\Packages\Provided;
use App\Runtime\ContextFactory;
use App\Sync\SyncSourceInstance;
use Gll\Hub\Contracts\Webhook\IncomingWebhook;
use Gll\Hub\Contracts\Webhook\SyncRequest;
use Gll\Hub\Contracts\Webhook\WebhookDefinition;
use Gll\Hub\Contracts\Webhook\WebhookReceiver;
use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;

/**
 * What the core does with a call from an outside service: let the package check
 * it and say what changed, then decide which instances to sync, and queue them.
 */
final class WebhookIntake
{
    public function __construct(
        private readonly Container $container,
        private readonly ContextFactory $contexts,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return int|null how many syncs were queued; null when the package did not accept the call */
    public function receive(Provided $webhook, IncomingWebhook $incoming): ?int
    {
        /** @var WebhookReceiver $receiver */
        $receiver = $this->container->make($webhook->class);
        /** @var WebhookDefinition $definition */
        $definition = $webhook->definition;
        $context = $this->contexts->forPackage($webhook->package);

        if (! $receiver->verify($context, $incoming)) {
            return null;
        }

        $queued = 0;
        foreach ($receiver->handle($context, $incoming) as $request) {
            if (! in_array($request->source, $definition->refreshes, true)) {
                $this->logger->warning('Webhook asked for a source it does not refresh', [
                    'package' => $webhook->package,
                    'webhook' => $definition->key,
                    'source' => $request->source,
                ]);
                continue;
            }
            foreach ($this->instancesFor($webhook->package, $request) as $instance) {
                SyncSourceInstance::dispatch($instance->id, $request->externalIds === [] ? null : $request->externalIds);
                $queued++;
            }
        }

        return $queued;
    }

    /** @return iterable<SourceInstance> the enabled instances whose settings hold every value the request names */
    private function instancesFor(string $package, SyncRequest $request): iterable
    {
        return SourceInstance::enabled()
            ->where('package', $package)
            ->where('source_key', $request->source)
            ->get()
            ->filter(function (SourceInstance $instance) use ($request): bool {
                foreach ($request->instance as $key => $value) {
                    if (! isset($instance->settings[$key]) || (string) $instance->settings[$key] !== (string) $value) {
                        return false;
                    }
                }

                return true;
            });
    }
}
