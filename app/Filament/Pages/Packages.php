<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Livewire\PackageSettingsForm;
use App\Packages\AcceptedPackage;
use App\Packages\PackageRegistry;
use App\Packages\Provided;
use App\Webhooks\WebhookUrl;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use UnitEnum;

/** What the installed packages provide, which were refused and why, and the settings they share. Reading only. */
class Packages extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Packages';

    protected static ?string $title = 'Packages';

    public function content(Schema $schema): Schema
    {
        $registry = app(PackageRegistry::class);
        $sections = [];

        foreach (array_values($registry->packages()) as $i => $package) {
            $sections[] = $this->accepted($package, $i);
        }
        if ($sections === []) {
            $sections[] = Section::make('No package installed')->schema([
                TextEntry::make('none')->hiddenLabel()->state('Add packages as the README describes, then reload this page.'),
            ]);
        }
        foreach (array_values($registry->rejected()) as $i => $rejected) {
            $sections[] = Section::make("Rejected: {$rejected->name}")
                ->description('The core does not run this package.')
                ->schema([TextEntry::make("rejected_{$i}")->label('Reasons')->state($rejected->reasons)->listWithLineBreaks()->bulleted()]);
        }

        return $schema->components($sections);
    }

    private function accepted(AcceptedPackage $package, int $i): Section
    {
        $manifest = $package->manifest;
        $entries = [];
        $lists = [
            'Content types' => array_map(fn (Provided $p): string => "{$p->definition->label} ({$p->key()})", $package->types),
            'Sources' => array_map(fn (Provided $p): string => "{$p->definition->label} ({$p->key()})", $package->sources),
            'Webhooks' => array_map(fn (Provided $p): string => "{$p->definition->label}: " . WebhookUrl::of($p), $package->webhooks),
            'Logins' => array_map(fn (Provided $p): string => "{$p->definition->label} ({$p->key()})", $package->logins),
        ];
        foreach ($lists as $label => $items) {
            if ($items !== []) {
                $entries[] = TextEntry::make('provides_' . Str::slug($label) . "_{$i}")->label($label)->state($items)->listWithLineBreaks()->bulleted();
            }
        }
        if ($package->settings() !== [] || $package->credentials() !== []) {
            $entries[] = Section::make('Settings')
                ->description('Shared by every source, webhook and login of the package.')
                ->schema([Livewire::make(PackageSettingsForm::class, ['package' => $manifest->name])->key("settings_{$i}")])
                ->compact();
        }

        return Section::make($manifest->label)
            ->description("{$manifest->name} · version {$manifest->version} · contract {$manifest->contracts}" . ($manifest->description !== '' ? " · {$manifest->description}" : ''))
            ->schema($entries === [] ? [TextEntry::make("empty_{$i}")->hiddenLabel()->state('Provides nothing.')] : $entries);
    }
}
