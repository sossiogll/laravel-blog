<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Content\Rules\Slug;
use PHPUnit\Framework\TestCase;

final class SlugTest extends TestCase
{
    public function test_a_title_becomes_a_url_safe_slug(): void
    {
        $this->assertSame('hello-world', Slug::from('Hello, World!'));
        $this->assertSame('cafe-au-lait', Slug::from('Café au lait'));
        $this->assertSame('already-a-slug', Slug::from('already-a-slug'));
    }

    public function test_it_is_never_empty(): void
    {
        $this->assertSame('entry', Slug::from('!!!'));
        $this->assertSame('entry', Slug::from(''));
    }

    public function test_it_leaves_room_for_a_suffix(): void
    {
        $slug = Slug::from(str_repeat('word ', 100));

        $this->assertLessThanOrEqual(150, strlen($slug));
        $this->assertStringEndsNotWith('-', $slug);
    }
}
