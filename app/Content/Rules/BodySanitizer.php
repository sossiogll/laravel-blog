<?php

declare(strict_types=1);

namespace App\Content\Rules;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * The one place HTML from outside becomes HTML the site may print. It runs once,
 * when a body is stored, so what is in the database is already safe and no source
 * carries a cleaner of its own.
 */
final class BodySanitizer
{
    private const MAX_INPUT = 5_000_000;

    private readonly HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $this->sanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowLinkSchemes(['https', 'http', 'mailto'])
                ->allowMediaSchemes(['https'])
                ->allowRelativeLinks(false)
                ->allowRelativeMedias(false)
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                ->withMaxInputLength(self::MAX_INPUT),
        );
    }

    public function html(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }
}
