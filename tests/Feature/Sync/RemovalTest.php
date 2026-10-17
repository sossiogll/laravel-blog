<?php

declare(strict_types=1);

namespace Tests\Feature\Sync;

use App\Models\Entry;
use Gll\Hub\Contracts\Exceptions\Unavailable;
use Gll\Hub\Contracts\Source\Cursor;
use Gll\Hub\Contracts\Source\SyncBatch;
use Tests\Fixtures\Entries;
use Tests\HubTestCase;

final class RemovalTest extends HubTestCase
{
    /** @param list<string> $ids */
    private function lists(string $source, array $ids): void
    {
        $this->script->entries[$source] = array_map(fn (string $id) => Entries::book($id, "Book {$id}"), $ids);
    }

    /** @return list<string> */
    private function live(): array
    {
        return Entry::orderBy('external_id')->pluck('external_id')->map(fn ($id) => (string) $id)->all();
    }

    public function test_a_source_that_lists_everything_removes_what_it_stops_listing(): void
    {
        $instance = $this->instance();
        $this->lists('catalog', ['1', '2', '3']);
        $this->sync($instance);

        $this->lists('catalog', ['1', '2']);
        $report = $this->sync($instance);

        $this->assertSame(1, $report->removed);
        $this->assertSame(['1', '2'], $this->live());
        $this->assertSame(['3'], Entry::onlyTrashed()->pluck('external_id')->all(), 'soft-deleted, not gone');
    }

    public function test_a_source_that_lists_only_the_latest_never_removes_on_absence(): void
    {
        $instance = $this->instance('latest');
        $this->lists('latest', ['1', '2', '3']);
        $this->sync($instance);

        $this->lists('latest', ['3']);
        $report = $this->sync($instance);

        $this->assertSame(0, $report->removed);
        $this->assertSame(['1', '2', '3'], $this->live());
    }

    public function test_a_listing_that_fails_midway_removes_nothing(): void
    {
        $instance = $this->instance();
        $this->lists('catalog', ['1', '2']);
        $this->sync($instance);

        $this->lists('catalog', ['1']);
        $this->script->failures['catalog'] = new Unavailable('the page 2 timed out');
        try {
            $this->sync($instance);
            $this->fail('the failure should have reached the caller');
        } catch (Unavailable) {
        }

        $this->assertSame(['1', '2'], $this->live());
    }

    public function test_an_entry_that_became_invalid_is_not_taken_for_a_removed_one(): void
    {
        $instance = $this->instance();
        $this->lists('catalog', ['1']);
        $this->sync($instance);

        $this->script->entries['catalog'] = [Entries::book('1', 'Broken now', ['fields' => []])];
        $report = $this->sync($instance);

        $this->assertSame(0, $report->removed);
        $this->assertSame(['1'], $this->live());
        $this->assertSame('Book 1', Entry::sole()->title);
    }

    public function test_removal_is_limited_to_the_instance_that_was_synced(): void
    {
        $first = $this->instance();
        $second = $this->instance('catalog', ['label' => 'Second']);
        $this->lists('catalog', ['1', '2']);
        $this->sync($first);
        $this->sync($second);

        $this->lists('catalog', ['1']);
        $this->sync($first);

        $this->assertSame(1, Entry::where('source_instance_id', $first->id)->count());
        $this->assertSame(2, Entry::where('source_instance_id', $second->id)->count());
    }

    public function test_an_incremental_source_removes_what_the_batch_names_and_nothing_else(): void
    {
        $instance = $this->instance('changes');
        $this->script->useBatches([new SyncBatch([Entries::book('1'), Entries::book('2'), Entries::book('3')], cursor: new Cursor('c1'))]);
        $this->sync($instance);

        // Entries 2 and 3 are not in this batch, which is only the changes: only "2" is gone.
        $this->script->useBatches([new SyncBatch([Entries::book('1', 'Renamed')], removed: ['2', 'unknown'], cursor: new Cursor('c2'))]);
        $report = $this->sync($instance);

        $this->assertSame(1, $report->removed);
        $this->assertSame(['1', '3'], $this->live());
    }

    public function test_a_single_fetch_that_returns_null_removes_that_entry_only(): void
    {
        $instance = $this->instance();
        $this->lists('catalog', ['1', '2']);
        $this->sync($instance);
        $this->script->calls = [];
        $this->script->fetchable = ['1' => null];

        $report = $this->sync($instance, ['1']);

        $this->assertSame(1, $report->removed);
        $this->assertSame(['2'], $this->live());
        $this->assertSame([['fetch', '1']], $this->script->calls, 'only the named entry is asked for');
    }

    public function test_a_single_fetch_updates_the_named_entry_and_removes_nothing_by_absence(): void
    {
        $instance = $this->instance();
        $this->lists('catalog', ['1', '2']);
        $this->sync($instance);
        $this->script->fetchable = ['1' => Entries::book('1', 'Fresh title')];

        $report = $this->sync($instance, ['1']);

        $this->assertSame(1, $report->updated);
        $this->assertSame(0, $report->removed);
        $this->assertSame(['Fresh title', 'Book 2'], Entry::orderBy('external_id')->pluck('title')->all());
    }

    public function test_named_entries_fall_back_to_a_full_sync_when_the_source_cannot_fetch_one(): void
    {
        $instance = $this->instance('latest');
        $this->lists('latest', ['1']);

        $report = $this->sync($instance, ['1']);

        $this->assertSame(1, $report->created);
        $this->assertSame([['pull', null]], $this->script->calls);
    }
}
