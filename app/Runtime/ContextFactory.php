<?php

declare(strict_types=1);

namespace App\Runtime;

use App\Models\PackageSetting;
use App\Models\SourceInstance;
use Closure;
use Gll\Hub\Contracts\Http\HttpClient;
use Gll\Hub\Contracts\Package\Context;
use Psr\Log\LoggerInterface;

/** Builds the Context of a call: package-wide values first, the instance's over them. */
final class ContextFactory
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** For calls that belong to a package and no instance, such as a webhook. */
    public function forPackage(string $package): Context
    {
        $shared = PackageSetting::find($package);

        return $this->context(
            $shared?->settings ?? [],
            static fn (): array => $shared?->credentials ?? [],
            ['package' => $package],
        );
    }

    public function forInstance(SourceInstance $instance): Context
    {
        $shared = PackageSetting::find($instance->package);

        return $this->context(
            $instance->settings + ($shared?->settings ?? []),
            static fn (): array => ($instance->credentials ?? []) + ($shared?->credentials ?? []),
            ['package' => $instance->package, 'source' => $instance->source_key, 'instance' => $instance->getKey()],
        );
    }

    /**
     * @param array<string, mixed> $settings
     * @param Closure(): array<string, string> $credentials
     * @param array<string, mixed> $tags
     */
    private function context(array $settings, Closure $credentials, array $tags): Context
    {
        return new CoreContext(
            settings: $settings,
            loadCredentials: $credentials,
            http: $this->http,
            logger: new TaggedLogger($this->logger, $tags),
            publicUrl: rtrim((string) config('app.url'), '/'),
        );
    }
}
