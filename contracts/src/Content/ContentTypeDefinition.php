<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Content;

/**
 * A kind of content: its key, its fields, and what the core's shared features do
 * with it. Title, slug, body, language, author, publication date, thumbnail,
 * comments and likes are the core's own and are not declared here.
 */
final readonly class ContentTypeDefinition
{
    /**
     * @param string $key stable identifier, lowercase, e.g. "project"
     * @param list<Field> $fields the type's own fields
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $pluralLabel,
        public array $fields,
        public bool $commentable = true,
        public bool $likeable = true,
        public bool $inFeed = false,
        public bool $notifiesSubscribers = false,
        public string $routePrefix = '',
    ) {
    }

    public function field(string $key): ?Field
    {
        foreach ($this->fields as $field) {
            if ($field->key === $key) {
                return $field;
            }
        }

        return null;
    }
}
