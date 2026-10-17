<?php

declare(strict_types=1);

namespace Gll\Hub\Contracts\Package;

/**
 * A value a package asks the person to configure. The admin draws the form from it;
 * the package reads the value through its context. Secrets are never settings: they
 * are credentials (see Credential).
 */
final readonly class Setting
{
    /**
     * @param list<string> $options for Enum: the allowed values
     * @param array<string, scalar>|null $visibleWhen shown only when other settings have these values
     */
    public function __construct(
        public string $key,
        public string $label,
        public SettingType $type,
        public mixed $default = null,
        public bool $required = false,
        public string $help = '',
        public array $options = [],
        public ?array $visibleWhen = null,
    ) {
    }
}
