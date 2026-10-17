<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Gll\Hub\Contracts\Package\Capability;
use Gll\Hub\Contracts\Package\Context;
use Gll\Hub\Contracts\Package\Credential;
use Gll\Hub\Contracts\Package\Setting;
use Gll\Hub\Contracts\Package\SettingType;
use Gll\Hub\Contracts\Source\ExternalEntry;
use Gll\Hub\Contracts\Source\FetchesSingleEntry;
use Gll\Hub\Contracts\Source\Source;
use Gll\Hub\Contracts\Source\SourceDefinition;

/** Lists everything it has, so what it stops listing is gone. */
final class CatalogSource implements FetchesSingleEntry, Source
{
    public function __construct(private readonly Script $script)
    {
    }

    public static function definition(): SourceDefinition
    {
        return new SourceDefinition(
            key: 'catalog',
            label: 'Catalog',
            produces: ['book'],
            capabilities: [Capability::SingleFetch],
            instanceSettings: [
                new Setting('shelf', 'Shelf', SettingType::String, required: true),
                new Setting('format', 'Format', SettingType::Enum, default: 'paper', options: ['paper', 'audio']),
                new Setting('copies', 'Copies', SettingType::Integer, default: 1),
                new Setting('narrator', 'Narrator', SettingType::String, visibleWhen: ['format' => 'audio']),
            ],
            instanceCredentials: [new Credential('token', 'API token', required: false)],
            defaultIntervalMinutes: 30,
        );
    }

    public function pull(Context $context): iterable
    {
        $this->script->calls[] = ['pull', $context->setting('shelf')];

        yield from $this->script->entries['catalog'] ?? [];

        if (isset($this->script->failures['catalog'])) {
            throw $this->script->failures['catalog'];
        }
    }

    public function fetch(Context $context, string $externalId): ?ExternalEntry
    {
        $this->script->calls[] = ['fetch', $externalId];

        return $this->script->fetchable[$externalId] ?? null;
    }
}
