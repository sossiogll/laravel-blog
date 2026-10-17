<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Gll\Hub\Contracts\Package\Capability;
use Gll\Hub\Contracts\Package\Context;
use Gll\Hub\Contracts\Source\Cursor;
use Gll\Hub\Contracts\Source\Source;
use Gll\Hub\Contracts\Source\SourceDefinition;
use Gll\Hub\Contracts\Source\SyncBatch;
use Gll\Hub\Contracts\Source\SyncsIncrementally;

/** Resumes from a cursor and names what was removed. */
final class ChangesSource implements Source, SyncsIncrementally
{
    public function __construct(private readonly Script $script)
    {
    }

    public static function definition(): SourceDefinition
    {
        return new SourceDefinition(
            key: 'changes',
            label: 'Changes',
            produces: ['book'],
            capabilities: [Capability::Incremental],
            defaultIntervalMinutes: 10,
        );
    }

    public function pull(Context $context): iterable
    {
        throw new \LogicException('An incremental source is read with pullSince()');
    }

    public function pullSince(Context $context, ?Cursor $cursor): SyncBatch
    {
        return $this->script->nextBatch($cursor);
    }
}
