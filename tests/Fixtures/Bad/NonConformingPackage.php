<?php

declare(strict_types=1);

namespace Tests\Fixtures\Bad;

use Gll\Hub\Contracts\Package\Manifest;
use Gll\Hub\Contracts\Package\Package;

/** Speaks the right contract but breaks its rules: a bad name, and a class that is not there. */
final class NonConformingPackage implements Package
{
    public function manifest(): Manifest
    {
        return new Manifest('Not A Name', 'Broken', '1.0.0', '1.0', ['source' => ['Tests\Fixtures\Bad\Missing']]);
    }
}
