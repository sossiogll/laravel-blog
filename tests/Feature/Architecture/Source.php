<?php

declare(strict_types=1);

namespace Tests\Feature\Architecture;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/** The PHP files of the core, for the tests that read the code itself. */
final class Source
{
    /**
     * @param list<string> $directories relative to the project root
     * @return array<string, string> contents by path relative to the project root
     */
    public static function files(array $directories): array
    {
        $root = dirname(__DIR__, 3);
        $files = [];

        foreach ($directories as $directory) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$root}/{$directory}", FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[substr($file->getPathname(), strlen($root) + 1)] = (string) file_get_contents($file->getPathname());
                }
            }
        }
        ksort($files);

        return $files;
    }

    /** @return list<string> the classes a file imports with `use` */
    public static function imports(string $code): array
    {
        preg_match_all('/^use\s+([^;{\s]+)\s*;/m', $code, $matches);

        return $matches[1];
    }
}
