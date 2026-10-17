<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Source;

/**
 * The format of ExternalEntry::$body. A source passes the body as its service gives
 * it; the core sanitizes and converts in ONE place, so no package carries its own
 * HTML cleaner or converter.
 */
enum BodyFormat: string
{
    case Markdown = 'markdown';
    case Html = 'html';
}
