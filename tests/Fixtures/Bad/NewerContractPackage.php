<?php

declare(strict_types=1);

namespace Tests\Fixtures\Bad;

use Gll\Hub\Contracts\Package\Manifest;
use Gll\Hub\Contracts\Package\Package;
use Tests\Fixtures\Book;

/** Built for a contract major this core does not speak. */
final class NewerContractPackage implements Package
{
    public function manifest(): Manifest
    {
        return new Manifest('acme/future', 'Future', '9.0.0', '2.0', ['content_type' => [Book::class]]);
    }
}
