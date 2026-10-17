<?php

declare(strict_types=1);

namespace App\Packages;

use Gll\Hub\Contracts\Auth\AuthDefinition;
use Gll\Hub\Contracts\Package\Credential;
use Gll\Hub\Contracts\Package\Manifest;
use Gll\Hub\Contracts\Package\Setting;
use Gll\Hub\Contracts\Webhook\WebhookDefinition;

final readonly class AcceptedPackage
{
    /**
     * @param list<Provided> $types
     * @param list<Provided> $sources
     * @param list<Provided> $webhooks
     * @param list<Provided> $logins
     */
    public function __construct(
        public Manifest $manifest,
        public array $types,
        public array $sources,
        public array $webhooks,
        public array $logins = [],
    ) {
    }

    /**
     * Everything the person configures once for the whole package: the manifest's own settings and those of its
     * logins. They share one namespace, as they share one context.
     *
     * @return list<Setting>
     */
    public function settings(): array
    {
        $settings = $this->manifest->settings;
        foreach ($this->logins as $login) {
            if ($login->definition instanceof AuthDefinition) {
                array_push($settings, ...$login->definition->settings);
            }
        }

        return self::distinct($settings);
    }

    /**
     * The secrets of the package: its own, its webhooks' (a signing secret) and its logins' (a client secret).
     *
     * @return list<Credential>
     */
    public function credentials(): array
    {
        $credentials = $this->manifest->credentials;
        foreach ([...$this->webhooks, ...$this->logins] as $provided) {
            if ($provided->definition instanceof WebhookDefinition || $provided->definition instanceof AuthDefinition) {
                array_push($credentials, ...$provided->definition->credentials);
            }
        }

        return self::distinct($credentials);
    }

    /**
     * @template T of Setting|Credential
     * @param list<T> $declared
     * @return list<T> the first declaration of each key
     */
    private static function distinct(array $declared): array
    {
        $seen = [];
        foreach ($declared as $one) {
            $seen[$one->key] ??= $one;
        }

        return array_values($seen);
    }
}
