<?php

declare(strict_types=1);

namespace Tests\Feature\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * The core never names a package: no integration, no package, no content type of
 * one. What it knows comes from the registry at run time.
 */
final class GoldenRuleTest extends TestCase
{
    /**
     * A word of an integration (whole words only, so a column type such as "mediumText" is fine), a package
     * of the gllhub vendor other than the contract, or a namespace of one.
     */
    private const FORBIDDEN = '/\b(?:medium|github|gitlab|bitbucket|portfolio|substack|wordpress|hashnode|linkedin|twitter|youtube|rss)\b'
        . '|gllhub\/(?!contracts\b)[a-z0-9-]+'
        . '|Gll\\\\Hub\\\\(?!Contracts\\\\)\w+/i';

    public function test_no_integration_or_package_is_named_in_the_core(): void
    {
        $offences = [];

        foreach (Source::files(['app', 'config', 'routes', 'database']) as $path => $code) {
            foreach (explode("\n", $code) as $number => $line) {
                if (preg_match(self::FORBIDDEN, $line, $match) === 1) {
                    $offences[] = $path . ':' . ($number + 1) . ' names "' . $match[0] . '"';
                }
            }
        }

        $this->assertSame([], $offences, "The core must not name a package:\n" . implode("\n", $offences));
    }

    public function test_the_scan_covers_the_core(): void
    {
        $files = array_keys(Source::files(['app', 'config', 'routes', 'database']));

        foreach (['app/Packages/PackageRegistry.php', 'app/Sync/SourceSynchronizer.php', 'config/gllhub.php', 'routes/webhooks.php', 'database/migrations/2026_10_17_190100_create_entries_table.php'] as $expected) {
            $this->assertContains($expected, $files);
        }
    }

    public function test_the_rule_catches_what_it_is_meant_to_catch(): void
    {
        foreach ([
            "if (\$source === 'github') {",
            'Medium::class',
            "'gllhub/medium'",
            'use Gll\Hub\Portfolio\Types\Project;',
            'composer require gllhub/anything',
            '// the Portfolio package',
            'an RSS feed',
        ] as $offending) {
            $this->assertSame(1, preg_match(self::FORBIDDEN, $offending), $offending);
        }
    }

    public function test_the_rule_leaves_the_contract_and_ordinary_words_alone(): void
    {
        foreach ([
            'use Gll\Hub\Contracts\Package\Package;',
            "'gllhub/contracts'",
            "\$table->mediumText('body');",
            'Schedule::command(\'gllhub:sync-due\')',
            "config('gllhub.sync.backoff')",
            'MediaLibrary media',
            'a mediumly sized thing',
        ] as $fine) {
            $this->assertSame(0, preg_match(self::FORBIDDEN, $fine), $fine);
        }
    }
}
