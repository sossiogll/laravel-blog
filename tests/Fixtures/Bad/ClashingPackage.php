<?php

declare(strict_types=1);

namespace Tests\Fixtures\Bad;

use Gll\Hub\Contracts\Package\Manifest;
use Gll\Hub\Contracts\Package\Package;
use Tests\Fixtures\Book;

/** Conforms, but declares a content type another package already owns. */
final class ClashingPackage implements Package
{
    public function manifest(): Manifest
    {
        return new Manifest('acme/clash', 'Clash', '1.0.0', '1.0', ['content_type' => [Book::class]]);
    }
}
