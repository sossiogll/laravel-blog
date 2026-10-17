<?php

declare(strict_types=1);

namespace App\Packages\Console;

use App\Packages\PackageRegistry;
use Illuminate\Console\Command;

final class ListPackages extends Command
{
    protected $signature = 'gllhub:packages';

    protected $description = 'List the installed gllHub packages: what each provides, and why a package was rejected';

    public function handle(PackageRegistry $registry): int
    {
        $accepted = [];
        foreach ($registry->packages() as $name => $package) {
            $accepted[] = [
                $name,
                $package->manifest->version,
                implode(', ', array_map(fn ($p) => $p->key(), $package->types)) ?: '-',
                implode(', ', array_map(fn ($p) => $p->key(), $package->sources)) ?: '-',
                implode(', ', array_map(fn ($p) => $p->key(), $package->webhooks)) ?: '-',
            ];
        }

        if ($accepted === [] && $registry->rejected() === []) {
            $this->info('No gllHub package is installed.');

            return self::SUCCESS;
        }

        if ($accepted !== []) {
            $this->table(['Package', 'Version', 'Content types', 'Sources', 'Webhooks'], $accepted);
        }
        foreach ($registry->rejected() as $rejected) {
            $this->error("Rejected {$rejected->name}");
            foreach ($rejected->reasons as $reason) {
                $this->line("  - {$reason}");
            }
        }

        return self::SUCCESS;
    }
}
