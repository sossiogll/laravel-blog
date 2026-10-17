<?php

declare(strict_types=1);

namespace App\Packages;

use Gll\Hub\Contracts\Package\Manifest;

final readonly class AcceptedPackage
{
    /**
     * @param list<Provided> $types
     * @param list<Provided> $sources
     * @param list<Provided> $webhooks
     */
    public function __construct(
        public Manifest $manifest,
        public array $types,
        public array $sources,
        public array $webhooks,
    ) {
    }
}
