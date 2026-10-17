<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Gll\Hub\Contracts\Package\Context;
use Gll\Hub\Contracts\Source\Source;
use Gll\Hub\Contracts\Source\SourceDefinition;

/** Shows only the latest items, like a feed: absence from it says nothing. */
final class LatestSource implements Source
{
    public function __construct(private readonly Script $script)
    {
    }

    public static function definition(): SourceDefinition
    {
        return new SourceDefinition(
            key: 'latest',
            label: 'Latest',
            produces: ['book'],
            defaultIntervalMinutes: 15,
            listsEverything: false,
        );
    }

    public function pull(Context $context): iterable
    {
        $this->script->calls[] = ['pull', null];

        yield from $this->script->entries['latest'] ?? [];
    }
}
