<?php

declare(strict_types=1);

namespace Tests\Feature\Sync;

use App\Models\Entry;
use App\Models\SyncStatus;
use App\Sync\DueInstances;
use App\Sync\SyncSourceInstance;
use Gll\Hub\Contracts\Exceptions\InvalidCredentials;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\Fixtures\Entries;
use Tests\HubTestCase;

final class SchedulingTest extends HubTestCase
{
    private Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::parse('2026-05-05 12:00:00', 'UTC');
        $this->travelTo($this->now);
    }

    /** @return list<int> */
    private function dueIds(): array
    {
        return $this->app->make(DueInstances::class)->at(Carbon::now('UTC'))->pluck('id')->sort()->values()->all();
    }

    public function test_an_instance_that_never_ran_is_due(): void
    {
        $instance = $this->instance();

        $this->assertSame([$instance->id], $this->dueIds());
    }

    public function test_an_instance_is_due_once_the_default_interval_of_its_source_has_elapsed(): void
    {
        // catalog declares 30 minutes.
        $recent = $this->instance(attributes: ['last_run_at' => $this->now->copy()->subMinutes(29)]);
        $elapsed = $this->instance(attributes: ['last_run_at' => $this->now->copy()->subMinutes(30)]);

        $this->assertSame([$elapsed->id], $this->dueIds());
        $this->assertNotSame($recent->id, $elapsed->id);
    }

    public function test_the_interval_of_the_instance_wins_over_the_default(): void
    {
        $short = $this->instance(attributes: ['interval_minutes' => 5, 'last_run_at' => $this->now->copy()->subMinutes(6)]);
        $this->instance(attributes: ['interval_minutes' => 120, 'last_run_at' => $this->now->copy()->subMinutes(60)]);

        $this->assertSame([$short->id], $this->dueIds());
    }

    public function test_an_interval_of_zero_means_on_demand_only(): void
    {
        $this->instance(attributes: ['interval_minutes' => 0]);

        $this->assertSame([], $this->dueIds());
    }

    public function test_disabled_and_unknown_instances_are_never_due(): void
    {
        $this->instance(attributes: ['enabled' => false]);
        $this->instance(attributes: ['package' => 'gone/package']);

        $this->assertSame([], $this->dueIds());
    }

    public function test_an_instance_waits_for_its_retry_time(): void
    {
        $waiting = $this->instance(attributes: ['status' => SyncStatus::Retrying, 'retry_after' => $this->now->copy()->addMinute()]);
        $ready = $this->instance(attributes: ['status' => SyncStatus::Retrying, 'retry_after' => $this->now->copy()->subMinute()]);

        $this->assertSame([$ready->id], $this->dueIds());
        $this->assertNotSame($waiting->id, $ready->id);
    }

    public function test_a_failing_instance_is_left_for_the_admin(): void
    {
        $this->instance(attributes: ['status' => SyncStatus::Failing]);
        $failed = $this->instance(attributes: ['status' => SyncStatus::Failed]);

        $this->assertSame([$failed->id], $this->dueIds());
    }

    public function test_the_due_command_queues_a_sync_for_each_due_instance(): void
    {
        Queue::fake();
        $due = $this->instance();
        $this->instance(attributes: ['interval_minutes' => 0]);

        $this->artisan('gllhub:sync-due')->expectsOutputToContain('Queued 1 sync(s).')->assertSuccessful();

        Queue::assertPushed(SyncSourceInstance::class, 1);
        Queue::assertPushed(SyncSourceInstance::class, fn (SyncSourceInstance $job) => $job->instanceId === $due->id && $job->externalIds === null);
    }

    public function test_the_due_command_is_scheduled_every_minute(): void
    {
        $events = collect($this->app->make(Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command, 'gllhub:sync-due'));

        $this->assertCount(1, $events);
        $this->assertSame('* * * * *', $events->first()->expression);
    }

    public function test_the_sync_command_runs_one_instance_now_and_reports_the_result(): void
    {
        $instance = $this->instance();
        $this->script->entries['catalog'] = [Entries::book('1')];

        $this->artisan('gllhub:sync', ['instance' => $instance->id])
            ->expectsOutputToContain("#{$instance->id} Library catalog: ok")
            ->assertSuccessful();

        $this->assertSame(1, Entry::count());
    }

    public function test_the_sync_command_fails_with_the_reason_when_the_run_fails(): void
    {
        $instance = $this->instance();
        $this->script->failures['catalog'] = new InvalidCredentials('the token was refused');

        $this->artisan('gllhub:sync', ['instance' => $instance->id])
            ->expectsOutputToContain('failing (the token was refused)')
            ->assertFailed();
    }

    public function test_the_sync_command_with_all_runs_every_enabled_instance(): void
    {
        $this->instance();
        $this->instance('latest');
        $this->instance(attributes: ['enabled' => false]);
        $this->script->entries['catalog'] = [Entries::book('1')];
        $this->script->entries['latest'] = [Entries::book('2')];

        $this->artisan('gllhub:sync', ['--all' => true])->assertSuccessful();

        $this->assertSame(['1', '2'], Entry::orderBy('external_id')->pluck('external_id')->map(fn ($id) => (string) $id)->all());
    }

    public function test_the_sync_command_needs_an_instance_that_exists(): void
    {
        $this->artisan('gllhub:sync')->expectsOutputToContain('Name an instance, or use --all.')->assertFailed();
        $this->artisan('gllhub:sync', ['instance' => 404])->expectsOutputToContain('There is no source instance 404.')->assertFailed();
    }
}
