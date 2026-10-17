<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Content\Rules\BodySanitizer;
use PHPUnit\Framework\TestCase;

final class BodySanitizerTest extends TestCase
{
    private function clean(string $html): string
    {
        return (new BodySanitizer())->html($html);
    }

    public function test_it_keeps_ordinary_markup(): void
    {
        $clean = $this->clean('<h2>Title</h2><p>Some <strong>bold</strong> and <em>italic</em> text.</p><ul><li>one</li></ul><blockquote>q</blockquote><pre><code>x1</code></pre>');

        foreach (['<h2>Title</h2>', '<strong>bold</strong>', '<em>italic</em>', '<li>one</li>', '<blockquote>q</blockquote>', '<code>x1</code>'] as $kept) {
            $this->assertStringContainsString($kept, $clean);
        }
    }

    public function test_it_removes_scripts_handlers_and_dangerous_links(): void
    {
        $clean = $this->clean('<p onclick="x()">Hi</p><script>alert(1)</script><a href="javascript:alert(1)">bad</a><a href="data:text/html;base64,AAAA">data</a><iframe src="https://e.test"></iframe><style>p{}</style>');

        foreach (['<script', 'onclick', 'javascript:', 'data:', '<iframe', '<style'] as $unsafe) {
            $this->assertStringNotContainsString($unsafe, $clean);
        }
        $this->assertStringContainsString('<p>Hi</p>', $clean);
    }

    public function test_links_open_safely_and_only_secure_images_are_kept(): void
    {
        $clean = $this->clean('<a href="https://a.test/x" target="_blank">a</a><img src="https://a.test/i.png" alt="i"><img src="http://a.test/plain.png"><img src="/relative.png"><a href="/relative">r</a>');

        $this->assertStringContainsString('href="https://a.test/x"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
        $this->assertStringContainsString('src="https://a.test/i.png"', $clean);
        $this->assertStringNotContainsString('plain.png', $clean);
        $this->assertStringNotContainsString('relative', $clean);
    }

    public function test_long_articles_are_not_truncated(): void
    {
        $long = str_repeat('<p>' . str_repeat('word ', 20) . '</p>', 1000);

        $this->assertSame(1000, substr_count($this->clean($long), '<p>'));
    }
}
