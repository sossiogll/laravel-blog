<?php

declare(strict_types=1);

namespace Tests\Feature\Sync;

use App\Models\Entry;
use Gll\Hub\Contracts\Exceptions\Unavailable;
use Gll\Hub\Contracts\Source\Cursor;
use Gll\Hub\Contracts\Source\SyncBatch;
use RuntimeException;
use Tests\Fixtures\Entries;
use Tests\HubTestCase;

final class IncrementalSyncTest extends HubTestCase
{
    public function test_batches_are_pulled_while_there_is_more_and_the_last_cursor_is_kept(): void
    {
        $instance = $this->makeInstance('changes');
        $this->script->useBatches([
            new SyncBatch([Entries::book('1')], cursor: new Cursor('c1'), hasMore: true),
            new SyncBatch([Entries::book('2')], cursor: new Cursor('c2'), hasMore: true),
            new SyncBatch([Entries::book('3')], cursor: new Cursor('c3')),
        ]);

        $report = $this->sync($instance);

        $this->assertSame(3, $report->created);
        $this->assertSame(['', 'c1', 'c2'], $this->script->called('since'), 'the first call has no cursor');
        $this->assertSame('c3', $instance->fresh()->cursor);
    }

    public function test_the_next_run_resumes_from_the_stored_cursor(): void
    {
        $instance = $this->makeInstance('changes', ['cursor' => 'c9']);
        $this->script->useBatches([new SyncBatch([], cursor: new Cursor('c10'))]);

        $this->sync($instance);

        $this->assertSame(['c9'], $this->script->called('since'));
        $this->assertSame('c10', $instance->fresh()->cursor);
    }

    public function test_a_batch_without_a_cursor_leaves_the_stored_one(): void
    {
        $instance = $this->makeInstance('changes', ['cursor' => 'keep']);
        $this->script->useBatches([new SyncBatch([Entries::book('1')])]);

        $this->sync($instance);

        $this->assertSame('keep', $instance->fresh()->cursor);
    }

    public function test_the_cursor_of_finished_batches_survives_a_failure_later_in_the_run(): void
    {
        $instance = $this->makeInstance('changes');
        $this->script->useBatches([
            new SyncBatch([Entries::book('1')], cursor: new Cursor('c1'), hasMore: true),
            new Unavailable('down'),
        ]);

        try {
            $this->sync($instance);
            $this->fail('the failure should have reached the caller');
        } catch (Unavailable) {
        }

        $this->assertSame('c1', $instance->fresh()->cursor, 'the next run resumes after the first batch');
        $this->assertSame(1, Entry::count());
    }

    public function test_a_source_that_says_more_without_moving_its_cursor_is_stopped(): void
    {
        $instance = $this->makeInstance('changes');
        $this->script->useBatches([new SyncBatch([], cursor: new Cursor('same'), hasMore: true)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cursor did not advance');

        try {
            $this->sync($instance);
        } finally {
            $this->assertCount(2, $this->script->calls, 'one call to set the cursor, one that proved it did not move');
        }
    }

    public function test_the_batch_limit_stops_a_source_that_keeps_moving_forever(): void
    {
        config(['gllhub.sync.max_batches' => 3]);
        $instance = $this->makeInstance('changes');
        $this->script->useBatches(array_map(
            fn (int $i) => new SyncBatch([], cursor: new Cursor("c{$i}"), hasMore: true),
            range(1, 10),
        ));

        $this->expectException(RuntimeException::class);

        try {
            $this->sync($instance);
        } finally {
            $this->assertCount(3, $this->script->calls);
        }
    }
}
