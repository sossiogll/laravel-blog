<?php

declare(strict_types=1);

namespace App\Runtime;

use Closure;
use DateTimeImmutable;
use Gll\Hub\Contracts\Http\HttpClient;
use Gll\Hub\Contracts\Package\Context;
use Illuminate\Support\Carbon;
use Psr\Log\LoggerInterface;

/**
 * What the core hands a package for one call. Settings are a snapshot; credentials
 * are read (and so decrypted) only when the package asks for one.
 */
final class CoreContext implements Context
{
    /** @var array<string, string>|null */
    private ?array $credentials = null;

    /**
     * @param array<string, mixed> $settings
     * @param Closure(): array<string, string> $loadCredentials
     */
    public function __construct(
        private readonly array $settings,
        private readonly Closure $loadCredentials,
        private readonly HttpClient $http,
        private readonly LoggerInterface $logger,
        private readonly string $publicUrl,
    ) {
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    public function settings(): array
    {
        return $this->settings;
    }

    public function credential(string $key): ?string
    {
        $this->credentials ??= ($this->loadCredentials)();

        return $this->credentials[$key] ?? null;
    }

    public function http(): HttpClient
    {
        return $this->http;
    }

    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    public function now(): DateTimeImmutable
    {
        return Carbon::now('UTC')->toDateTimeImmutable();
    }

    public function publicUrl(): string
    {
        return $this->publicUrl;
    }
}
