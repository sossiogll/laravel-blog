<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Package;

/**
 * The entry point of a gllHub package. The core finds it through the package's
 * composer.json ("extra": {"gllhub": {"package": "Vendor\\Name\\SomePackage"}}),
 * instantiates it with no arguments and reads its manifest.
 */
interface Package
{
    public function manifest(): Manifest;
}
