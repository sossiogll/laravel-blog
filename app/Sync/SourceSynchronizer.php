<?php

declare(strict_types=1);

namespace App\Sync;

use App\Content\EntryRemover;
use App\Content\ReferenceResolver;
use App\Models\SourceInstance;
use App\Packages\PackageRegistry;
use App\Runtime\ContextFactory;
use Gll\Hub\Contracts\Package\Capability;
use Gll\Hub\Contracts\Package\Context;
use Gll\Hub\Contracts\Source\Cursor;
use Gll\Hub\Contracts\Source\FetchesSingleEntry;
use Gll\Hub\Contracts\Source\Source;
use Gll\Hub\Contracts\Source\SourceDefinition;
use Gll\Hub\Contracts\Source\SyncsIncrementally;
use Illuminate\Contracts\Container\Container;
use RuntimeException;

/**
 * Runs one source instance: asks the source for data in the way it declares it
 * can give it, and has every entry stored. The source decides nothing; what is
 * kept, what is removed and when, is decided here.
 *
 * Exceptions are not caught here: the job turns their class into a status.
 */
final class SourceSynchronizer
{
    public function __construct(
        private readonly PackageRegistry $registry,
        private readonly ContextFactory $contexts,
        private readonly Container $container,
        private readonly EntryUpserter $upserter,
        private readonly EntryRemover $remover,
        private readonly ReferenceResolver $references,
    ) {
    }

    /** @param list<string>|null $externalIds only these entries, when the source can fetch one by id */
    public function run(SourceInstance $instance, ?array $externalIds = null): SyncReport
    {
        $provided = $this->registry->source($instance->package, $instance->source_key)
            ?? throw SourceNotInstalled::for($instance->package, $instance->source_key);

        /** @var SourceDefinition $definition */
        $definition = $provided->definition;
        $source = $this->container->make($provided->class);
        $context = $this->contexts->forInstance($instance);
        $report = new SyncReport();

        if ($externalIds !== null && $externalIds !== [] && $definition->has(Capability::SingleFetch)) {
            /** @var FetchesSingleEntry $source */
            $this->fetchEach($instance, $definition, $source, $context, $externalIds, $report);
        } elseif ($definition->has(Capability::Incremental)) {
            /** @var SyncsIncrementally $source */
            $this->pullIncrementally($instance, $definition, $source, $context, $report);
        } else {
            /** @var Source $source */
            $this->pullEverything($instance, $definition, $source, $context, $report);
        }

        $this->references->resolve();

        return $report;
    }

    private function pullEverything(SourceInstance $instance, SourceDefinition $definition, Source $source, Context $context, SyncReport $report): void
    {
        $seen = [];
        foreach ($source->pull($context) as $entry) {
            // Seen even when invalid: an entry we refuse to store is not an entry that is gone.
            $seen[] = $entry->externalId;
            $this->upserter->apply($instance, $definition, $entry, $report);
        }

        // Only a listing that ran to its end, from a source that lists everything, proves absence.
        if ($definition->listsEverything) {
            $report->removed += $this->remover->removeUnseen($instance->id, $seen);
        }
    }

    private function pullIncrementally(SourceInstance $instance, SourceDefinition $definition, SyncsIncrementally $source, Context $context, SyncReport $report): void
    {
        $cursor = $instance->cursor === null ? null : new Cursor($instance->cursor);
        $batches = 0;

        do {
            $batch = $source->pullSince($context, $cursor);

            foreach ($batch->entries as $entry) {
                $this->upserter->apply($instance, $definition, $entry, $report);
            }
            $report->removed += $this->remover->remove($instance->id, $batch->removed);

            $advanced = $batch->cursor !== null && $batch->cursor->value !== $cursor?->value;
            if ($batch->cursor !== null) {
                // Saved per batch: a crash later in the run resumes here, not from the start.
                $instance->forceFill(['cursor' => $batch->cursor->value])->save();
                $cursor = $batch->cursor;
            }

            if ($batch->hasMore && (! $advanced || ++$batches >= (int) config('gllhub.sync.max_batches'))) {
                throw new RuntimeException('The source says there is more but its cursor did not advance; stopped to avoid looping.');
            }
        } while ($batch->hasMore);
    }

    /** @param list<string> $externalIds */
    private function fetchEach(SourceInstance $instance, SourceDefinition $definition, FetchesSingleEntry $source, Context $context, array $externalIds, SyncReport $report): void
    {
        foreach ($externalIds as $externalId) {
            $entry = $source->fetch($context, $externalId);

            if ($entry === null) {
                $report->removed += $this->remover->remove($instance->id, [$externalId]);
            } else {
                $this->upserter->apply($instance, $definition, $entry, $report);
            }
        }
    }
}
