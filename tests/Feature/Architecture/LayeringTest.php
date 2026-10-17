<?php

declare(strict_types=1);

namespace Tests\Feature\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * Layering is one-way: HTTP and console call application services; those use the
 * domain and the persistence; the domain imports neither persistence nor HTTP, and
 * persistence knows nothing of the services above it.
 *
 * Folders say which layer a class is in (see layerOf), so a class cannot dodge a rule by name.
 */
final class LayeringTest extends TestCase
{
    private const DOMAIN = 'domain';
    private const PERSISTENCE = 'persistence';
    private const APPLICATION = 'application';
    private const ENTRY = 'entry';
    private const WIRING = 'wiring';

    /** What each layer must not import: a namespace prefix ending in a backslash, or an exact class. */
    private const FORBIDDEN = [
        self::DOMAIN => [
            'App\\Models\\', 'App\\Http\\', 'App\\Filament\\', 'App\\Sync\\', 'App\\Runtime\\', 'App\\Webhooks\\', 'App\\Providers\\',
            'App\\Content\\',
            'Illuminate\\Database\\', 'Illuminate\\Http\\', 'Illuminate\\Queue\\', 'Illuminate\\Bus\\',
            'Illuminate\\Contracts\\Queue\\', 'Illuminate\\Console\\', 'Illuminate\\Support\\Facades\\',
            'Spatie\\',
        ],
        self::PERSISTENCE => [
            'App\\Http\\', 'App\\Filament\\', 'App\\Sync\\', 'App\\Runtime\\', 'App\\Webhooks\\', 'App\\Providers\\',
            'App\\Content\\', 'Illuminate\\Http\\', 'Illuminate\\Console\\',
        ],
        self::APPLICATION => [
            'App\\Http\\', 'App\\Filament\\', 'App\\Providers\\', 'Illuminate\\Http\\Request', 'Illuminate\\Http\\JsonResponse',
            'Illuminate\\Http\\Response', 'Illuminate\\Http\\RedirectResponse', 'Illuminate\\Console\\',
        ],
    ];

    public static function layerOf(string $path): string
    {
        $path = substr($path, strlen('app/'));

        return match (true) {
            str_starts_with($path, 'Providers/') => self::WIRING,
            str_starts_with($path, 'Http/'), str_starts_with($path, 'Filament/'), str_contains($path, '/Console/') => self::ENTRY,
            str_starts_with($path, 'Models/') => self::PERSISTENCE,
            str_starts_with($path, 'Packages/'), str_starts_with($path, 'Content/Rules/') => self::DOMAIN,
            str_starts_with($path, 'Content/'), str_starts_with($path, 'Sync/'), str_starts_with($path, 'Runtime/'), str_starts_with($path, 'Webhooks/') => self::APPLICATION,
            default => 'unclassified',
        };
    }

    /** @return list<string> */
    private function violations(string $path, string $code): array
    {
        $layer = self::layerOf($path);
        $found = [];

        foreach (Source::imports($code) as $import) {
            // The rules are domain: any layer may use them.
            if (str_starts_with($import, 'App\\Content\\Rules\\')) {
                continue;
            }
            foreach (self::FORBIDDEN[$layer] ?? [] as $forbidden) {
                // A console class of another folder is a console class wherever it is imported from.
                if (str_starts_with($import, $forbidden) || ($layer !== self::ENTRY && preg_match('/^App\\\\\w+\\\\Console\\\\/', $import) === 1)) {
                    $found[] = "{$path} ({$layer}) imports {$import}";
                    break;
                }
            }
        }

        return $found;
    }

    public function test_every_class_is_in_a_known_layer(): void
    {
        $unclassified = [];
        foreach (array_keys(Source::files(['app'])) as $path) {
            if (self::layerOf($path) === 'unclassified') {
                $unclassified[] = $path;
            }
        }

        $this->assertSame([], $unclassified, 'Add the folder to LayeringTest::layerOf, deliberately.');
    }

    public function test_no_layer_imports_what_it_must_not(): void
    {
        $violations = [];
        foreach (Source::files(['app']) as $path => $code) {
            array_push($violations, ...$this->violations($path, $code));
        }

        $this->assertSame([], $violations, "Layering is broken:\n" . implode("\n", $violations));
    }

    public function test_the_layers_hold_what_we_expect(): void
    {
        $this->assertSame(self::DOMAIN, self::layerOf('app/Packages/PackageRegistry.php'));
        $this->assertSame(self::ENTRY, self::layerOf('app/Packages/Console/ListPackages.php'));
        $this->assertSame(self::DOMAIN, self::layerOf('app/Content/Rules/BodySanitizer.php'));
        $this->assertSame(self::APPLICATION, self::layerOf('app/Content/EntryWriter.php'));
        $this->assertSame(self::PERSISTENCE, self::layerOf('app/Models/Entry.php'));
        $this->assertSame(self::ENTRY, self::layerOf('app/Http/Controllers/WebhookController.php'));
        $this->assertSame(self::ENTRY, self::layerOf('app/Filament/Resources/Entries/EntryResource.php'));
    }

    public function test_a_violation_is_detected(): void
    {
        $this->assertNotSame([], $this->violations('app/Packages/Bad.php', "<?php\nuse App\\Models\\Entry;\n"));
        $this->assertNotSame([], $this->violations('app/Content/Rules/Bad.php', "<?php\nuse Illuminate\\Support\\Facades\\DB;\n"));
        $this->assertNotSame([], $this->violations('app/Models/Bad.php', "<?php\nuse App\\Sync\\SourceSynchronizer;\n"));
        $this->assertNotSame([], $this->violations('app/Sync/Bad.php', "<?php\nuse Illuminate\\Http\\Request;\n"));
        $this->assertNotSame([], $this->violations('app/Content/Bad.php', "<?php\nuse App\\Filament\\Fields\\FieldFactory;\n"));
        $this->assertNotSame([], $this->violations('app/Sync/Bad.php', "<?php\nuse App\\Packages\\Console\\ListPackages;\n"));
        $this->assertSame([], $this->violations('app/Http/Fine.php', "<?php\nuse App\\Models\\Entry;\nuse Illuminate\\Http\\Request;\n"));
        $this->assertSame([], $this->violations('app/Sync/Fine.php', "<?php\nuse App\\Models\\Entry;\nuse App\\Packages\\PackageRegistry;\n"));
    }
}
