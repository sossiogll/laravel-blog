<?php

declare(strict_types=1);

namespace App\Packages;

use Gll\Hub\Contracts\Content\ContentTypeDefinition;
use Gll\Hub\Contracts\Source\SourceDefinition;
use Gll\Hub\Contracts\Webhook\WebhookDefinition;

/**
 * A class an accepted package provides, with the definition it declared. Read
 * once at registration, so nothing asks a package class what it is twice.
 */
final readonly class Provided
{
    public function __construct(
        public string $package,
        /** @var class-string */
        public string $class,
        public ContentTypeDefinition|SourceDefinition|WebhookDefinition $definition,
    ) {
    }

    public function key(): string
    {
        return $this->definition->key;
    }
}
