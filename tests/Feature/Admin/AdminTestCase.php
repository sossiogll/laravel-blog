<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Entry;
use App\Models\User;
use Filament\Facades\Filament;
use Tests\HubTestCase;

/** The admin panel with the fixture package installed, used by a signed-in user. */
abstract class AdminTestCase extends HubTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
        $this->actingAs(User::factory()->create());
    }

    /** @param array<string, mixed> $attributes */
    protected function handWritten(string $title, array $attributes = []): Entry
    {
        return Entry::create($attributes + ['type' => 'book', 'slug' => str($title)->slug()->value(), 'title' => $title, 'fields' => ['isbn' => '1']]);
    }
}
