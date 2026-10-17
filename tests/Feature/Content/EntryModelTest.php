<?php

declare(strict_types=1);

namespace Tests\Feature\Content;

use App\Models\Entry;
use App\Models\PackageSetting;
use App\Models\SourceInstance;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Tests\HubTestCase;

final class EntryModelTest extends HubTestCase
{
    private function entry(array $attributes = []): Entry
    {
        return Entry::create($attributes + ['type' => 'book', 'slug' => 'dune', 'title' => 'Dune']);
    }

    public function test_fields_and_curated_are_arrays_that_default_to_empty(): void
    {
        $entry = $this->entry()->fresh();

        $this->assertSame([], $entry->fields);
        $this->assertSame([], $entry->curated);
        $this->assertSame('en', $entry->language);

        $entry->update(['fields' => ['isbn' => '1'], 'curated' => ['featured' => true]]);

        $this->assertSame(['isbn' => '1'], $entry->fresh()->fields);
        $this->assertSame(['featured' => true], $entry->fresh()->curated);
    }

    public function test_a_slug_is_unique_per_type_and_language_even_for_a_trashed_entry(): void
    {
        $first = $this->entry();
        $this->entry(['language' => 'it']);
        $this->entry(['type' => 'essay']);

        $first->delete();

        $this->expectException(UniqueConstraintViolationException::class);
        $this->entry();
    }

    public function test_an_entry_of_a_source_is_unique_by_instance_and_external_id(): void
    {
        $instance = $this->instance();
        $this->entry(['slug' => 'a'])->forceFill(['source_instance_id' => $instance->id, 'external_id' => '1'])->save();
        // Entries written by hand have no instance and no external id, and any number of them is fine.
        $this->entry(['slug' => 'b']);
        $this->entry(['slug' => 'c']);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->entry(['slug' => 'd'])->forceFill(['source_instance_id' => $instance->id, 'external_id' => '1'])->save();
    }

    public function test_removing_a_source_instance_keeps_its_content(): void
    {
        $instance = $this->instance();
        $this->entry()->forceFill(['source_instance_id' => $instance->id, 'external_id' => '1'])->save();

        $instance->delete();

        $this->assertNull(Entry::sole()->source_instance_id);
    }

    public function test_media_has_a_collection_for_each_media_field_and_the_thumbnail(): void
    {
        $entry = $this->entry();

        $this->assertTrue($entry->getMediaCollection('cover')->singleFile, 'a single Media field keeps one file');
        $this->assertFalse($entry->getMediaCollection('gallery')->singleFile, 'a multiple Media field keeps many');
        $this->assertTrue($entry->getMediaCollection('thumbnail')->singleFile);
        $this->assertNull($entry->getMediaCollection('isbn'), 'only Media fields get a collection');
    }

    public function test_an_entry_of_an_unknown_type_still_has_its_thumbnail(): void
    {
        $entry = $this->entry(['type' => 'from-a-removed-package']);

        $this->assertNotNull($entry->getMediaCollection('thumbnail'));
        $this->assertNull($entry->getMediaCollection('cover'));
    }

    public function test_credentials_are_encrypted_at_rest_and_never_serialized(): void
    {
        $instance = $this->instance(attributes: ['credentials' => ['token' => 'abc-secret-token']]);
        $package = PackageSetting::create(['package' => 'acme/library', 'settings' => ['a' => 1], 'credentials' => ['secret' => 'xyz-secret-value']]);

        foreach ([$instance, $package] as $model) {
            $this->assertArrayNotHasKey('credentials', $model->toArray());
            $this->assertStringNotContainsString('-secret-', $model->toJson());
        }
        $this->assertSame(['a' => 1], $package->toArray()['settings']);

        $this->assertStringNotContainsString('abc-secret-token', DB::table('source_instances')->value('credentials'));
        $this->assertStringNotContainsString('xyz-secret-value', DB::table('package_settings')->value('credentials'));
        $this->assertSame(['token' => 'abc-secret-token'], SourceInstance::find($instance->id)->credentials);
        $this->assertSame(['secret' => 'xyz-secret-value'], PackageSetting::find('acme/library')->credentials);
    }

    public function test_a_new_instance_is_enabled_and_pending(): void
    {
        $instance = SourceInstance::create(['package' => 'acme/library', 'source_key' => 'catalog', 'label' => 'L']);

        $fresh = $instance->fresh();
        $this->assertTrue($fresh->enabled);
        $this->assertSame('pending', $fresh->status->value);
        $this->assertSame([], $fresh->settings);
        $this->assertNull($fresh->credentials);
        $this->assertNull($fresh->interval_minutes);
        $this->assertNull($fresh->cursor);
    }
}
