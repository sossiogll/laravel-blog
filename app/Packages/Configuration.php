<?php

declare(strict_types=1);

namespace App\Packages;

use Gll\Hub\Contracts\Package\Credential;
use Gll\Hub\Contracts\Package\Setting;

/**
 * What the core stores when a person configures a package or one of its source instances.
 *
 * A package's context only returns what was saved, so a default has to be saved with the rest: it is
 * applied here, once, when the configuration is written, and not at every read.
 */
final class Configuration
{
    /**
     * @param list<Setting> $declared
     * @param array<string, mixed> $given the values typed in; a blank one stands for "the default"
     * @return array<string, mixed> every declared setting that has a value
     */
    public static function settings(array $declared, array $given): array
    {
        $values = [];
        foreach ($declared as $setting) {
            $value = $given[$setting->key] ?? null;
            if ($value === '' || $value === []) {
                $value = null;
            }
            $value ??= $setting->default;

            if ($value !== null) {
                $values[$setting->key] = $value;
            }
        }

        return $values;
    }

    /**
     * A secret is never shown back, so the person cannot retype it to keep it: blank means "keep what is stored".
     *
     * @param list<Credential> $declared
     * @param array<string, mixed> $given
     * @param array<string, string>|null $stored
     * @return array<string, string>|null null when no secret is left
     */
    public static function credentials(array $declared, array $given, ?array $stored): ?array
    {
        $kept = [];
        foreach ($declared as $credential) {
            $typed = $given[$credential->key] ?? null;
            $value = is_string($typed) && $typed !== '' ? $typed : ($stored[$credential->key] ?? null);

            if ($value !== null && $value !== '') {
                $kept[$credential->key] = $value;
            }
        }

        return $kept === [] ? null : $kept;
    }
}
