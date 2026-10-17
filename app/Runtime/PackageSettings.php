<?php

declare(strict_types=1);

namespace App\Runtime;

use App\Models\PackageSetting;
use App\Packages\AcceptedPackage;
use App\Packages\Configuration;
use Illuminate\Database\UniqueConstraintViolationException;

/** Saves the settings and secrets a package shares between its sources, webhooks and logins. */
final class PackageSettings
{
    /**
     * @param array<string, mixed> $settings values as typed; defaults are stored for the blank ones
     * @param array<string, mixed> $credentials a blank secret keeps the stored one
     */
    public function save(AcceptedPackage $package, array $settings, array $credentials): PackageSetting
    {
        $name = $package->manifest->name;
        $write = static function () use ($name, $package, $settings, $credentials): PackageSetting {
            $stored = PackageSetting::find($name);

            return PackageSetting::updateOrCreate(['package' => $name], [
                'settings' => Configuration::settings($package->settings(), $settings),
                'credentials' => Configuration::credentials($package->credentials(), $credentials, $stored?->credentials),
            ]);
        };

        try {
            return $write();
        } catch (UniqueConstraintViolationException) {
            // First save of the package, raced by another: the row exists now and this is an update.
            return $write();
        }
    }
}
