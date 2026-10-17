<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Source;

/**
 * A file the entry refers to. The core downloads it into its own media library,
 * so the site never depends on the outside service to serve it.
 */
final readonly class RemoteMedia
{
    /**
     * @param string $field the Media field it fills, or "thumbnail" for the core's own
     * @param string $url an absolute https URL the core can download
     * @param array<string, string> $headers extra request headers (e.g. a token for a private repo)
     */
    public function __construct(
        public string $field,
        public string $url,
        public string $alt = '',
        public ?string $filename = null,
        public array $headers = [],
    ) {
    }
}
