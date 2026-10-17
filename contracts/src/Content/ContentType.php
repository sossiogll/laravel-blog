<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Content;

/**
 * A content type a package adds to the core. Pure declaration: the core stores,
 * validates, versions, translates and serves the entries; the type only says what
 * an entry looks like.
 */
interface ContentType
{
    public static function definition(): ContentTypeDefinition;
}
