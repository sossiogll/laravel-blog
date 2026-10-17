<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Filament\Livewire\PackageSettingsForm;
use App\Filament\Pages\Packages;
use App\Filament\Resources\Entries\EntryResource;
use App\Models\PackageSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\Fixtures\Bad\NonConformingPackage;

final class PanelTest extends AdminTestCase
{
    public function test_a_guest_is_sent_to_the_login(): void
    {
        Auth::logout();

        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_the_panel_loads_for_a_signed_in_user(): void
    {
        $this->get('/admin')->assertOk()->assertSee('gllHub')->assertSee('Books');
    }

    public function test_there_is_one_navigation_item_per_content_type_in_the_content_group(): void
    {
        $items = EntryResource::getNavigationItems();

        $this->assertCount(1, $items);
        $this->assertSame('Books', $items[0]->getLabel());
        $this->assertSame('Content', $items[0]->getGroup());
        $this->assertStringContainsString('tab=book', $items[0]->getUrl());
    }

    public function test_the_packages_page_lists_accepted_and_rejected_packages(): void
    {
        $this->registry->register(new NonConformingPackage());

        Livewire::test(Packages::class)
            ->assertSee('Library')
            ->assertSee('acme/library')
            ->assertSee('version 1.2.0')
            ->assertSee('Book (book)')
            ->assertSee('Catalog (catalog)')
            ->assertSee('https://hub.example.test/webhooks/acme/library/push')
            ->assertSee('Rejected: ' . $this->registry->rejected()[0]->name)
            ->assertSee($this->registry->rejected()[0]->reasons[0]);
    }

    public function test_package_wide_settings_get_their_defaults_and_keep_a_secret_left_blank(): void
    {
        Livewire::test(PackageSettingsForm::class, ['package' => 'acme/library'])
            ->fillForm(['credentials' => ['key' => 'abc', 'secret' => 'hook']])
            ->call('save')
            ->assertHasNoErrors();

        $stored = PackageSetting::find('acme/library');
        $this->assertSame(['region' => 'eu'], $stored->settings);
        $this->assertSame(['key' => 'abc', 'secret' => 'hook'], $stored->credentials);

        Livewire::test(PackageSettingsForm::class, ['package' => 'acme/library'])
            ->assertDontSee('abc')
            ->fillForm(['settings' => ['region' => 'us'], 'credentials' => ['key' => '', 'secret' => '']])
            ->call('save');

        $stored = PackageSetting::find('acme/library');
        $this->assertSame(['region' => 'us'], $stored->settings);
        $this->assertSame(['key' => 'abc', 'secret' => 'hook'], $stored->credentials);
    }
}
