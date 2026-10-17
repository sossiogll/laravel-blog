<?php

declare(strict_types=1);

namespace App\Filament\Livewire;

use App\Filament\Fields\FieldFactory;
use App\Filament\Fields\FormValues;
use App\Models\PackageSetting;
use App\Packages\AcceptedPackage;
use App\Packages\PackageRegistry;
use App\Runtime\PackageSettings;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/** The package-wide settings and secrets of one package, drawn from what it declares. */
final class PackageSettingsForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public string $package = '';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(string $package): void
    {
        $this->package = $package;
        $this->accepted() ?? abort(404);

        // Secrets are not loaded: they are write-only.
        $this->form->fill(['settings' => PackageSetting::find($package)?->settings ?? []]);
    }

    public function form(Schema $schema): Schema
    {
        $package = $this->accepted();
        $stored = array_keys(PackageSetting::find($this->package)?->credentials ?? []);

        return $schema
            ->components([
                ...FieldFactory::settings($package?->settings() ?? []),
                ...FieldFactory::credentials($package?->credentials() ?? [], $stored),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function save(): void
    {
        $package = $this->accepted() ?? abort(404);
        $data = $this->form->getState();

        app(PackageSettings::class)->save($package, FormValues::settings($package->settings(), $data['settings'] ?? []), $data['credentials'] ?? []);

        // Typed secrets are gone from the page once saved.
        $this->form->fill(['settings' => PackageSetting::find($this->package)?->settings ?? []]);
        Notification::make()->success()->title('Saved')->send();
    }

    public function render(): View
    {
        return view('filament.livewire.package-settings-form');
    }

    private function accepted(): ?AcceptedPackage
    {
        return app(PackageRegistry::class)->package($this->package);
    }
}
