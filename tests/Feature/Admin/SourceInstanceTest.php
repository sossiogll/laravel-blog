<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Filament\Resources\SourceInstances\Pages\CreateSourceInstance;
use App\Filament\Resources\SourceInstances\Pages\EditSourceInstance;
use App\Filament\Resources\SourceInstances\Pages\ListSourceInstances;
use App\Models\SourceInstance;
use App\Models\SyncStatus;
use App\Sync\SyncSourceInstance;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

final class SourceInstanceTest extends AdminTestCase
{
    public function test_an_instance_is_created_from_the_declarations_with_defaults_and_encrypted_credentials(): void
    {
        Livewire::test(CreateSourceInstance::class)
            ->fillForm([
                'source' => 'acme/library/catalog',
                'label' => 'Main shelf',
                'settings' => ['shelf' => 'a'],
                'credentials' => ['token' => 's3cret-token'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $instance = SourceInstance::sole();
        $this->assertSame('acme/library', $instance->package);
        $this->assertSame('catalog', $instance->source_key);
        $this->assertTrue($instance->enabled);
        $this->assertNull($instance->interval_minutes);
        // The defaults were stored, the setting that is not visible for this format was not.
        $this->assertSame(['shelf' => 'a', 'format' => 'paper', 'copies' => 1], $instance->settings);
        $this->assertSame(['token' => 's3cret-token'], $instance->credentials);
        $this->assertStringNotContainsString('s3cret-token', (string) DB::table('source_instances')->value('credentials'));
    }

    public function test_a_required_setting_is_asked_for(): void
    {
        Livewire::test(CreateSourceInstance::class)
            ->fillForm(['source' => 'acme/library/catalog', 'label' => 'Main'])
            ->call('create')
            ->assertHasFormErrors(['settings.shelf']);

        $this->assertSame(0, SourceInstance::count());
    }

    public function test_a_credential_is_never_shown_back_and_a_blank_one_keeps_the_stored_secret(): void
    {
        $instance = $this->makeInstance(attributes: ['credentials' => ['token' => 's3cret-token']]);

        Livewire::test(EditSourceInstance::class, ['record' => $instance->getKey()])
            ->assertDontSee('s3cret-token')
            ->fillForm(['label' => 'Renamed', 'credentials' => ['token' => ''], 'interval_minutes' => '0'])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $instance->fresh();
        $this->assertSame('Renamed', $fresh->label);
        $this->assertSame(0, $fresh->interval_minutes);
        $this->assertSame(['token' => 's3cret-token'], $fresh->credentials);

        Livewire::test(EditSourceInstance::class, ['record' => $instance->getKey()])
            ->fillForm(['credentials' => ['token' => 'rotated']])
            ->call('save');

        $this->assertSame(['token' => 'rotated'], $instance->fresh()->credentials);
    }

    public function test_fixing_a_failing_instance_lets_the_scheduler_ask_again(): void
    {
        $instance = $this->makeInstance(attributes: ['status' => SyncStatus::Failing, 'last_error' => 'Bad token']);

        Livewire::test(EditSourceInstance::class, ['record' => $instance->getKey()])->call('save');

        $this->assertSame(SyncStatus::Pending, $instance->fresh()->status);
    }

    public function test_sync_now_queues_the_core_job_and_says_so(): void
    {
        Bus::fake();
        $instance = $this->makeInstance();

        Livewire::test(ListSourceInstances::class)
            ->assertCanSeeTableRecords([$instance])
            ->callTableAction('sync', $instance)
            ->assertNotified('Sync queued');

        Bus::assertDispatched(SyncSourceInstance::class, fn (SyncSourceInstance $job): bool => $job->instanceId === $instance->id && $job->externalIds === null);
    }

    public function test_a_disabled_instance_cannot_be_synced_and_can_be_enabled(): void
    {
        $instance = $this->makeInstance(attributes: ['enabled' => false]);

        Livewire::test(ListSourceInstances::class)
            ->assertTableActionHidden('sync', $instance)
            ->callTableAction('toggle', $instance);

        $this->assertTrue($instance->fresh()->enabled);
    }
}
