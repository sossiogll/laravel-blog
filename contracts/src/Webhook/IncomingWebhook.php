<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Webhook;

/** The raw request, framework-free. Header names are lowercase. */
final readonly class IncomingWebhook
{
    /**
     * @param array<string, string> $headers
     * @param array<string, string> $query
     */
    public function __construct(
        public string $body,
        public array $headers = [],
        public array $query = [],
    ) {
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    /** @return array<mixed> */
    public function json(): array
    {
        $decoded = json_decode($this->body, true);

        return is_array($decoded) ? $decoded : [];
    }
}
