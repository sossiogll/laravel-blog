<?php

declare(strict_types=1);

namespace App\Packages;

use Gll\Hub\Contracts\Package\Package;
use Throwable;

/**
 * Finds the packages Composer installed and hands them to the registry.
 *
 * A package names its entry point in its own composer.json
 * (`extra.gllhub.package`); Composer copies that into installed.json, the one
 * file that has the `extra` of every installed package.
 */
final class PackageDiscovery
{
    public function __construct(private readonly string $installedJson)
    {
    }

    public function populate(PackageRegistry $registry): PackageRegistry
    {
        foreach ($this->entryPoints() as $composerName => $class) {
            if (! class_exists($class)) {
                $registry->reject($composerName, ["entry point {$class} does not exist"]);
            } elseif (! is_subclass_of($class, Package::class)) {
                $registry->reject($composerName, ["entry point {$class} does not implement " . Package::class]);
            } else {
                try {
                    $registry->register(new $class(), $composerName);
                } catch (Throwable $e) {
                    $registry->reject($composerName, ["entry point {$class} cannot be created: {$e->getMessage()}"]);
                }
            }
        }

        return $registry;
    }

    /** @return array<string, string> entry point class by Composer package name */
    public function entryPoints(): array
    {
        if (! is_file($this->installedJson)) {
            return [];
        }

        $installed = json_decode((string) file_get_contents($this->installedJson), true);
        // Composer 2 wraps the list in {"packages": [...]}; Composer 1 wrote the bare list.
        $packages = is_array($installed) ? ($installed['packages'] ?? $installed) : [];

        $found = [];
        foreach ($packages as $package) {
            $class = $package['extra']['gllhub']['package'] ?? null;
            if (is_string($class) && is_string($package['name'] ?? null)) {
                $found[$package['name']] = $class;
            }
        }
        ksort($found);

        return $found;
    }
}
