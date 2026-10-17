<?php

declare(strict_types=1);

namespace Tests\Feature\Sync;

use App\Models\Entry;
use App\Models\SourceInstance;
use App\Models\SyncStatus;
use App\Sync\Failure;
use App\Sync\InstanceHealth;
use App\Sync\SourceSynchronizer;
use App\Sync\SyncSourceInstance;
use Gll\Hub\Contracts\Exceptions\InvalidConfiguration;
use Gll\Hub\Contracts\Exceptions\InvalidCredentials;
use Gll\Hub\Contracts\Exceptions\RateLimited;
use Gll\Hub\Contracts\Exceptions\Unavailable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Exceptions;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Tests\Fixtures\Entries;
use Tests\HubTestCase;
use Throwable;

final class SyncJobTest extends HubTestCase
{
    private function runJob(SourceInstance $instance, ?array $ids = null): SyncSourceInstance
    {
        $job = (new SyncSourceInstance($instance->id, $ids))->withFakeQueueInteractions();
        $job->handle($this->app->make(SourceSynchronizer::class), $this->app->make(InstanceHealth::class), $this->app->make(LoggerInterface::class));

        return $job;
    }

    private function failWith(Throwable $e): SourceInstance
    {
        $this->script->failures['catalog'] = $e;

        return $this->instance();
    }

    public function test_a_good_run_records_its_success(): void
    {
        $this->travelTo('2026-05-05 10:00:00');
        $instance = $this->instance(attributes: ['status' => SyncStatus::Failed, 'last_error' => 'old', 'retry_after' => now()->addHour()]);
        $this->script->entries['catalog'] = [Entries::book('1')];

        $job = $this->runJob($instance);

        $fresh = $instance->fresh();
        $this->assertSame(SyncStatus::Ok, $fresh->status);
        $this->assertNull($fresh->last_error);
        $this->assertNull($fresh->retry_after);
        $this->assertSame('2026-05-05 10:00:00', $fresh->last_run_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-05-05 10:00:00', $fresh->last_success_at->format('Y-m-d H:i:s'));
        $this->assertSame(1, Entry::count());
        $job->assertNotReleased();
    }

    public function test_a_disabled_or_missing_instance_is_left_alone(): void
    {
        $disabled = $this->instance(attributes: ['enabled' => false]);

        $this->runJob($disabled);
        (new SyncSourceInstance(999))->handle($this->app->make(SourceSynchronizer::class), $this->app->make(InstanceHealth::class), $this->app->make(LoggerInterface::class));

        $this->assertSame([], $this->script->calls);
        $this->assertSame(SyncStatus::Pending, $disabled->fresh()->status);
        $this->assertNull($disabled->fresh()->last_run_at);
    }

    public function test_unavailable_is_retried_after_a_backoff(): void
    {
        $this->travelTo('2026-05-05 10:00:00');
        $instance = $this->failWith(new Unavailable('api.test answered 503'));

        $job = $this->runJob($instance);

        $fresh = $instance->fresh();
        $this->assertSame(SyncStatus::Retrying, $fresh->status);
        $this->assertSame('api.test answered 503', $fresh->last_error);
        $this->assertSame('2026-05-05 10:01:00', $fresh->retry_after->format('Y-m-d H:i:s'));
        $job->assertReleased(60);
    }

    public function test_the_backoff_doubles_up_to_a_ceiling_and_then_gives_up(): void
    {
        $this->assertSame(60, Failure::of(new Unavailable(), 1)->retryIn);
        $this->assertSame(120, Failure::of(new Unavailable(), 2)->retryIn);
        $this->assertSame(240, Failure::of(new Unavailable(), 3)->retryIn);

        config(['gllhub.sync.max_backoff' => 100, 'gllhub.sync.max_attempts' => 5]);
        $this->assertSame(100, Failure::of(new Unavailable(), 3)->retryIn);

        $last = Failure::of(new Unavailable('still down'), 5);
        $this->assertSame(SyncStatus::Failed, $last->status);
        $this->assertNull($last->retryIn);
        $this->assertStringContainsString('Gave up after 5 attempts: still down', $last->message);
    }

    public function test_rate_limited_is_released_for_as_long_as_the_service_asked(): void
    {
        $this->travelTo('2026-05-05 10:00:00');
        $instance = $this->failWith(new RateLimited(300, 'slow down'));

        $job = $this->runJob($instance);

        $fresh = $instance->fresh();
        $this->assertSame(SyncStatus::Retrying, $fresh->status);
        $this->assertSame('2026-05-05 10:05:00', $fresh->retry_after->format('Y-m-d H:i:s'));
        $job->assertReleased(300);
    }

    public function test_invalid_credentials_stop_the_instance_without_a_retry(): void
    {
        $instance = $this->failWith(new InvalidCredentials('the token was refused'));

        $job = $this->runJob($instance);

        $fresh = $instance->fresh();
        $this->assertSame(SyncStatus::Failing, $fresh->status);
        $this->assertSame('the token was refused', $fresh->last_error);
        $this->assertNull($fresh->retry_after);
        $job->assertNotReleased();
    }

    public function test_invalid_configuration_names_the_setting_and_does_not_retry(): void
    {
        $instance = $this->failWith(new InvalidConfiguration('shelf', 'there is no such shelf'));

        $job = $this->runJob($instance);

        $this->assertSame(SyncStatus::Failing, $instance->fresh()->status);
        $this->assertSame('shelf: there is no such shelf', $instance->fresh()->last_error);
        $job->assertNotReleased();
    }

    public function test_anything_else_is_a_bug_that_is_reported_and_not_retried(): void
    {
        Exceptions::fake();
        $instance = $this->failWith(new RuntimeException('boom'));

        $job = $this->runJob($instance);

        $fresh = $instance->fresh();
        $this->assertSame(SyncStatus::Failed, $fresh->status);
        $this->assertStringContainsString('boom', $fresh->last_error);
        $this->assertStringContainsString(RuntimeException::class, $fresh->last_error);
        Exceptions::assertReported(fn (RuntimeException $e) => $e->getMessage() === 'boom');
        $job->assertNotReleased();
    }

    public function test_contract_failures_are_not_reported_as_bugs(): void
    {
        Exceptions::fake();
        $instance = $this->failWith(new InvalidCredentials());

        $this->runJob($instance);

        Exceptions::assertNothingReported();
    }

    public function test_a_failure_keeps_the_time_of_the_last_success(): void
    {
        $this->travelTo('2026-05-05 10:00:00');
        $instance = $this->instance();
        $this->runJob($instance);

        $this->travelTo('2026-05-05 11:00:00');
        $this->script->failures['catalog'] = new InvalidCredentials('expired');
        $this->runJob($instance);

        $fresh = $instance->fresh();
        $this->assertSame('2026-05-05 11:00:00', $fresh->last_run_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-05-05 10:00:00', $fresh->last_success_at->format('Y-m-d H:i:s'));
    }

    public function test_an_instance_whose_package_is_gone_fails_for_the_admin_to_see(): void
    {
        $instance = $this->instance(attributes: ['package' => 'gone/package']);

        $job = $this->runJob($instance);

        $this->assertSame(SyncStatus::Failing, $instance->fresh()->status);
        $this->assertStringContainsString('gone/package/catalog is not installed', $instance->fresh()->last_error);
        $job->assertNotReleased();
    }

    public function test_when_the_queue_gives_up_the_status_does_not_stay_retrying(): void
    {
        $instance = $this->instance(attributes: ['status' => SyncStatus::Retrying]);

        (new SyncSourceInstance($instance->id))->failed(new RuntimeException('too many attempts'));

        $this->assertSame(SyncStatus::Failed, $instance->fresh()->status);
        $this->assertStringContainsString('too many attempts', $instance->fresh()->last_error);
    }

    public function test_the_same_sync_is_queued_once_and_syncs_of_one_instance_do_not_overlap(): void
    {
        $this->assertSame((new SyncSourceInstance(1))->uniqueId(), (new SyncSourceInstance(1))->uniqueId());
        $this->assertNotSame((new SyncSourceInstance(1))->uniqueId(), (new SyncSourceInstance(2))->uniqueId());
        $this->assertNotSame((new SyncSourceInstance(1))->uniqueId(), (new SyncSourceInstance(1, ['a']))->uniqueId());
        $this->assertSame((new SyncSourceInstance(1, ['a', 'b']))->uniqueId(), (new SyncSourceInstance(1, ['b', 'a']))->uniqueId());

        $middleware = (new SyncSourceInstance(1))->middleware();
        $this->assertInstanceOf(WithoutOverlapping::class, $middleware[0]);
    }

    public function test_the_job_runs_through_the_queue_end_to_end(): void
    {
        $instance = $this->instance();
        $this->script->entries['catalog'] = [Entries::book('1'), Entries::book('2')];

        SyncSourceInstance::dispatchSync($instance->id);

        $this->assertSame(2, Entry::count());
        $this->assertSame(SyncStatus::Ok, $instance->fresh()->status);
    }
}
