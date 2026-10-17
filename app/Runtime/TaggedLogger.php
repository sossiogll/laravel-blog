<?php

declare(strict_types=1);

namespace App\Runtime;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * A package's logger: the core's, with the package (and instance) on every line,
 * without touching the shared channel's own context.
 */
final class TaggedLogger extends AbstractLogger
{
    /** @param array<string, mixed> $tags */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly array $tags,
    ) {
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $this->tags + $context);
    }
}
