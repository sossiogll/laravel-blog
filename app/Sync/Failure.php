<?php

declare(strict_types=1);

namespace App\Sync;

use App\Models\SyncStatus;
use Gll\Hub\Contracts\Exceptions\InvalidConfiguration;
use Gll\Hub\Contracts\Exceptions\InvalidCredentials;
use Gll\Hub\Contracts\Exceptions\RateLimited;
use Gll\Hub\Contracts\Exceptions\Unavailable;
use Throwable;

/**
 * What a failed run means, decided from the class of the exception and never from
 * its message: whether to try again and when, what to show the admin, and whether
 * it is a bug that someone must hear about.
 */
final readonly class Failure
{
    public function __construct(
        public SyncStatus $status,
        public string $message,
        /** Seconds until the next attempt; null = none. */
        public ?int $retryIn = null,
        /** True for anything not in the contract: a bug of the package, or of the core. */
        public bool $unexpected = false,
    ) {
    }

    public static function of(Throwable $e, int $attempt): self
    {
        return match (true) {
            $e instanceof Unavailable => $attempt >= (int) config('gllhub.sync.max_attempts')
                ? new self(SyncStatus::Failed, "Gave up after {$attempt} attempts: " . self::said($e, 'the service is unavailable'))
                : new self(SyncStatus::Retrying, self::said($e, 'The service is unavailable.'), self::backoff($attempt)),
            $e instanceof RateLimited => new self(SyncStatus::Retrying, self::said($e, 'The service asked to slow down.'), max(1, $e->retryAfter)),
            $e instanceof InvalidCredentials => new self(SyncStatus::Failing, self::said($e, 'The credentials are missing or were refused.')),
            $e instanceof InvalidConfiguration => new self(SyncStatus::Failing, "{$e->setting}: " . self::said($e, 'invalid value')),
            $e instanceof SourceNotInstalled => new self(SyncStatus::Failing, $e->getMessage()),
            default => new self(SyncStatus::Failed, 'Unexpected error: ' . $e::class . ': ' . $e->getMessage(), unexpected: true),
        };
    }

    private static function said(Throwable $e, string $fallback): string
    {
        return $e->getMessage() === '' ? $fallback : $e->getMessage();
    }

    /** Doubles from the configured base on each attempt, up to the configured ceiling. */
    private static function backoff(int $attempt): int
    {
        return min(
            (int) config('gllhub.sync.backoff') * 2 ** max(0, $attempt - 1),
            (int) config('gllhub.sync.max_backoff'),
        );
    }
}
