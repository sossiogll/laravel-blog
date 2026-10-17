<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Testing;

use DateTimeImmutable;
use Gll\Hub\Contracts\Http\HttpClient;
use Gll\Hub\Contracts\Package\Context;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * The Context every package tests with: fixed settings, credentials, clock and
 * HTTP client. Never test a package by reaching into the core.
 */
final class StaticContext implements Context
{
    /**
     * @param array<string, mixed> $settings
     * @param array<string, string> $credentials
     */
    public function __construct(
        private array $settings = [],
        private array $credentials = [],
        private HttpClient $http = new FakeHttpClient(),
        private ?DateTimeImmutable $now = null,
        private string $publicUrl = 'https://hub.example.test',
        private ?LoggerInterface $logger = null,
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
        return $this->credentials[$key] ?? null;
    }

    public function http(): HttpClient
    {
        return $this->http;
    }

    public function logger(): LoggerInterface
    {
        return $this->logger ??= new NullLogger();
    }

    public function now(): DateTimeImmutable
    {
        return $this->now ?? new DateTimeImmutable('2026-01-01T00:00:00Z');
    }

    public function publicUrl(): string
    {
        return $this->publicUrl;
    }
}
