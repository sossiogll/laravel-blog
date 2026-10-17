<?php

declare(strict_types=1);

namespace Tests\Feature\Packages;

use App\Packages\PackageDiscovery;
use App\Packages\PackageRegistry;
use Gll\Hub\Contracts\Package\Manifest;
use Gll\Hub\Contracts\Package\Package;
use stdClass;
use Tests\Fixtures\Bad\ClashingPackage;
use Tests\Fixtures\Bad\NewerContractPackage;
use Tests\Fixtures\Bad\NonConformingPackage;
use Tests\Fixtures\Bad\ThrowingPackage;
use Tests\Fixtures\Book;
use Tests\Fixtures\CatalogSource;
use Tests\Fixtures\LibraryPackage;
use Tests\Fixtures\PushWebhook;
use Tests\TestCase;

final class PackageRegistryTest extends TestCase
{
    public function test_a_conforming_package_is_accepted_and_exposes_what_it_provides(): void
    {
        $registry = new PackageRegistry();

        $this->assertTrue($registry->register(new LibraryPackage()));

        $this->assertSame(['acme/library'], array_keys($registry->packages()));
        $this->assertSame('Library', $registry->package('acme/library')->manifest->label);
        $this->assertSame(['book'], array_keys($registry->contentTypes()));
        $this->assertSame('Book', $registry->contentType('book')->label);
        $this->assertNull($registry->contentType('film'));

        $source = $registry->source('acme/library', 'catalog');
        $this->assertSame(CatalogSource::class, $source->class);
        $this->assertSame('catalog', $source->definition->key);
        $this->assertSame(['acme/library/catalog', 'acme/library/latest', 'acme/library/changes'], array_keys($registry->sources()));
        $this->assertNull($registry->source('acme/library', 'nope'));
        $this->assertNull($registry->source('other/library', 'catalog'));

        $this->assertSame(PushWebhook::class, $registry->webhook('acme/library', 'push')->class);
        $this->assertNull($registry->webhook('acme/library', 'catalog'));
        $this->assertSame([], $registry->rejected());
    }

    public function test_a_package_built_for_another_contract_major_is_rejected_with_the_reason(): void
    {
        $registry = new PackageRegistry();

        $this->assertFalse($registry->register(new NewerContractPackage()));

        $this->assertSame([], $registry->packages());
        $this->assertSame([], $registry->contentTypes());
        $this->assertSame('acme/future', $registry->rejected()[0]->name);
        $this->assertStringContainsString('declares contracts 2.0', implode("\n", $registry->rejected()[0]->reasons));
    }

    public function test_a_package_built_on_a_newer_minor_is_rejected(): void
    {
        $registry = new PackageRegistry();
        $newerMinor = new class () implements Package {
            public function manifest(): Manifest
            {
                return new Manifest('acme/minor', 'Minor', '1.0.0', '1.7', ['content_type' => [Book::class]]);
            }
        };

        $this->assertFalse($registry->register($newerMinor));
        $this->assertStringContainsString('declares contracts 1.7', $registry->rejected()[0]->reasons[0]);
    }

    public function test_a_package_that_fails_conformance_is_rejected_with_every_violation(): void
    {
        $registry = new PackageRegistry();

        $this->assertFalse($registry->register(new NonConformingPackage()));

        $reasons = implode("\n", $registry->rejected()[0]->reasons);
        $this->assertSame('Not A Name', $registry->rejected()[0]->name);
        $this->assertStringContainsString('is not a vendor/package name', $reasons);
        $this->assertStringContainsString('does not exist', $reasons);
    }

    public function test_a_package_that_throws_is_rejected_and_never_crashes_the_app(): void
    {
        $registry = new PackageRegistry();

        $this->assertFalse($registry->register(new ThrowingPackage(), 'acme/throws'));

        $this->assertSame('acme/throws', $registry->rejected()[0]->name);
        $this->assertStringContainsString('the manifest cannot be built', $registry->rejected()[0]->reasons[0]);
    }

    public function test_a_content_type_key_belongs_to_one_package(): void
    {
        $registry = new PackageRegistry();
        $registry->register(new LibraryPackage());

        $this->assertFalse($registry->register(new ClashingPackage()));

        $this->assertSame(['acme/library'], array_keys($registry->packages()));
        $this->assertStringContainsString('content type "book" is already provided by acme/library', $registry->rejected()[0]->reasons[0]);
    }

    public function test_a_package_registered_twice_is_rejected_the_second_time(): void
    {
        $registry = new PackageRegistry();
        $registry->register(new LibraryPackage());

        $this->assertFalse($registry->register(new LibraryPackage()));
        $this->assertStringContainsString('already registered', $registry->rejected()[0]->reasons[0]);
    }

    public function test_a_bad_package_does_not_stop_the_good_ones(): void
    {
        $registry = new PackageRegistry();
        $registry->register(new NewerContractPackage());
        $registry->register(new ThrowingPackage());

        $this->assertTrue($registry->register(new LibraryPackage()));
        $this->assertCount(2, $registry->rejected());
        $this->assertNotNull($registry->source('acme/library', 'catalog'));
    }

    public function test_discovery_reads_the_entry_points_composer_recorded(): void
    {
        $installed = tempnam(sys_get_temp_dir(), 'installed');
        file_put_contents($installed, json_encode(['packages' => [
            ['name' => 'acme/library', 'extra' => ['gllhub' => ['package' => LibraryPackage::class]]],
            ['name' => 'acme/ghost', 'extra' => ['gllhub' => ['package' => 'Acme\\Ghost\\Package']]],
            ['name' => 'acme/wrong', 'extra' => ['gllhub' => ['package' => stdClass::class]]],
            ['name' => 'acme/throws-on-new', 'extra' => ['gllhub' => ['package' => ThrowingPackage::class]]],
            ['name' => 'acme/plain-library', 'extra' => ['branch-alias' => []]],
            ['name' => 'acme/no-extra'],
        ]]));

        try {
            $registry = (new PackageDiscovery($installed))->populate(new PackageRegistry());
        } finally {
            unlink($installed);
        }

        $this->assertSame(['acme/library'], array_keys($registry->packages()));
        $rejected = array_column(array_map(fn ($r) => ['name' => $r->name, 'reasons' => $r->reasons], $registry->rejected()), 'reasons', 'name');
        $this->assertSame(['acme/ghost', 'acme/throws-on-new', 'acme/wrong'], array_keys($rejected));
        $this->assertStringContainsString('does not exist', $rejected['acme/ghost'][0]);
        $this->assertStringContainsString('does not implement', $rejected['acme/wrong'][0]);
    }

    public function test_discovery_without_composer_data_finds_nothing(): void
    {
        $registry = (new PackageDiscovery('/nonexistent/installed.json'))->populate(new PackageRegistry());

        $this->assertSame([], $registry->packages());
        $this->assertSame([], $registry->rejected());
    }

    public function test_the_registry_is_built_once_per_process(): void
    {
        $this->assertSame(app(PackageRegistry::class), app(PackageRegistry::class));
    }

    public function test_the_packages_command_lists_accepted_and_rejected_packages(): void
    {
        $registry = new PackageRegistry();
        $registry->register(new LibraryPackage());
        $registry->register(new NewerContractPackage());
        $this->app->instance(PackageRegistry::class, $registry);

        $this->artisan('gllhub:packages')
            ->expectsOutputToContain('catalog, latest, changes')
            ->expectsOutputToContain('Rejected acme/future')
            ->expectsOutputToContain('declares contracts 2.0')
            ->assertSuccessful();
    }

    public function test_the_packages_command_says_when_nothing_is_installed(): void
    {
        $this->app->instance(PackageRegistry::class, new PackageRegistry());

        $this->artisan('gllhub:packages')->expectsOutputToContain('No gllHub package is installed.')->assertSuccessful();
    }
}
