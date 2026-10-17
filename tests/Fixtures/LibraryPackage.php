<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Gll\Hub\Contracts\Package\Manifest;
use Gll\Hub\Contracts\Package\Package;

/** An invented package with every role the sync touches. Core tests use it and never a real one. */
final class LibraryPackage implements Package
{
    public function manifest(): Manifest
    {
        return new Manifest(
            name: 'acme/library',
            label: 'Library',
            version: '1.2.0',
            contracts: '1.0',
            provides: [
                'content_type' => [Book::class],
                'source' => [CatalogSource::class, LatestSource::class, ChangesSource::class],
                'webhook' => [PushWebhook::class],
            ],
        );
    }
}
