<?php

declare(strict_types=1);

namespace Tests\Fixtures\Bad;

use Gll\Hub\Contracts\Package\Manifest;
use Gll\Hub\Contracts\Package\Package;
use RuntimeException;

final class ThrowingPackage implements Package
{
    public function manifest(): Manifest
    {
        throw new RuntimeException('the manifest cannot be built');
    }
}
