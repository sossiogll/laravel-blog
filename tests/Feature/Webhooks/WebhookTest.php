<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use App\Models\PackageSetting;
use App\Models\SourceInstance;
use App\Sync\SyncSourceInstance;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\HubTestCase;

final class WebhookTest extends HubTestCase
{
    private const URL = '/webhooks/acme/library/push';

    private SourceInstance $shelfA;

    private SourceInstance $shelfB;

    private SourceInstance $disabled;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        PackageSetting::create(['package' => 'acme/library', 'settings' => [], 'credentials' => ['secret' => 's3cret']]);
        $this->shelfA = $this->instance();
        $this->shelfB = $this->instance(attributes: ['settings' => ['shelf' => 'b']]);
        $this->disabled = $this->instance(attributes: ['enabled' => false]);
    }

    /** @param array<string, mixed> $request */
    private function push(array $request, string $secret = 's3cret'): TestResponse
    {
        return $this->postJson(self::URL, ['requests' => [$request]], ['X-Secret' => $secret]);
    }

    /** @return list<int> */
    private function queuedInstances(): array
    {
        return Queue::pushed(SyncSourceInstance::class)->map(fn (SyncSourceInstance $job) => $job->instanceId)->sort()->values()->all();
    }

    public function test_a_verified_call_queues_the_sync_of_the_instances_it_names(): void
    {
        $this->push(['source' => 'catalog', 'instance' => ['shelf' => 'a'], 'ids' => ['1', '2']])
            ->assertStatus(202)
            ->assertExactJson(['queued' => 1]);

        Queue::assertPushed(SyncSourceInstance::class, 1);
        Queue::assertPushed(SyncSourceInstance::class, fn (SyncSourceInstance $job) => $job->instanceId === $this->shelfA->id && $job->externalIds === ['1', '2']);
    }

    public function test_without_ids_the_whole_instance_is_synced(): void
    {
        $this->push(['source' => 'catalog', 'instance' => ['shelf' => 'b']])->assertStatus(202);

        Queue::assertPushed(SyncSourceInstance::class, fn (SyncSourceInstance $job) => $job->instanceId === $this->shelfB->id && $job->externalIds === null);
    }

    public function test_without_an_instance_filter_every_enabled_instance_of_the_source_is_synced(): void
    {
        $this->instance('latest');

        $this->push(['source' => 'catalog'])->assertStatus(202)->assertExactJson(['queued' => 2]);

        $this->assertSame([$this->shelfA->id, $this->shelfB->id], $this->queuedInstances());
    }

    public function test_the_values_of_the_request_are_compared_as_text(): void
    {
        $numbered = $this->instance(attributes: ['settings' => ['shelf' => 5]]);

        $this->push(['source' => 'catalog', 'instance' => ['shelf' => '5']])->assertStatus(202);

        $this->assertSame([$numbered->id], $this->queuedInstances());
    }

    public function test_an_unmatched_request_queues_nothing_but_is_still_accepted(): void
    {
        $this->push(['source' => 'catalog', 'instance' => ['shelf' => 'zzz']])->assertStatus(202)->assertExactJson(['queued' => 0]);

        Queue::assertNothingPushed();
    }

    public function test_a_source_the_webhook_does_not_refresh_is_ignored(): void
    {
        $this->push(['source' => 'other'])->assertStatus(202)->assertExactJson(['queued' => 0]);

        Queue::assertNothingPushed();
    }

    public function test_a_call_the_package_does_not_verify_is_refused(): void
    {
        $this->push(['source' => 'catalog'], 'wrong')->assertStatus(401);
        $this->postJson(self::URL, ['requests' => [['source' => 'catalog']]])->assertStatus(401);

        Queue::assertNothingPushed();
    }

    public function test_a_webhook_without_a_configured_secret_verifies_nothing(): void
    {
        PackageSetting::query()->delete();

        $this->push(['source' => 'catalog'], '')->assertStatus(401);

        Queue::assertNothingPushed();
    }

    public function test_an_unknown_webhook_is_not_found(): void
    {
        $this->postJson('/webhooks/acme/library/nope')->assertNotFound();
        $this->postJson('/webhooks/other/package/push')->assertNotFound();
    }

    public function test_only_post_is_served(): void
    {
        $this->get(self::URL)->assertStatus(405);
    }

    public function test_the_route_has_no_web_middleware_so_no_session_and_no_csrf(): void
    {
        $route = Route::getRoutes()->getByName('webhooks');

        $this->assertSame(['throttle:webhooks'], $route->gatherMiddleware());
    }

    public function test_calls_are_rate_limited_per_caller_and_endpoint(): void
    {
        config(['gllhub.webhooks.per_minute' => 2]);

        $this->push(['source' => 'catalog'])->assertStatus(202);
        $this->push(['source' => 'catalog'])->assertStatus(202);
        $this->push(['source' => 'catalog'])->assertStatus(429);
    }
}
