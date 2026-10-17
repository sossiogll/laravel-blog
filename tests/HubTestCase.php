<?php

declare(strict_types=1);

namespace Tests;

use App\Models\SourceInstance;
use App\Packages\PackageRegistry;
use App\Sync\SourceSynchronizer;
use App\Sync\SyncReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\LibraryPackage;
use Tests\Fixtures\Script;

/** A database, a registry holding only the fixture package, and a script for its sources. */
abstract class HubTestCase extends TestCase
{
    use RefreshDatabase;

    protected Script $script;

    protected PackageRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->script = new Script();
        $this->app->instance(Script::class, $this->script);

        $this->registry = new PackageRegistry();
        $this->registry->register(new LibraryPackage());
        $this->app->instance(PackageRegistry::class, $this->registry);
    }

    /** @param array<string, mixed> $attributes */
    protected function instance(string $source = 'catalog', array $attributes = []): SourceInstance
    {
        // forceFill: status, cursor and the like are the core's to write, not mass-assignable.
        $instance = (new SourceInstance())->forceFill($attributes + [
            'package' => 'acme/library',
            'source_key' => $source,
            'label' => "Library {$source}",
            'settings' => ['shelf' => 'a'],
        ]);
        $instance->save();

        return $instance;
    }

    /** @param list<string>|null $externalIds */
    protected function sync(SourceInstance $instance, ?array $externalIds = null): SyncReport
    {
        return $this->app->make(SourceSynchronizer::class)->run($instance, $externalIds);
    }
}
