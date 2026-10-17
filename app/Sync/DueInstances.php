<?php

declare(strict_types=1);

namespace App\Sync;

use App\Models\SourceInstance;
use App\Models\SyncStatus;
use App\Packages\PackageRegistry;
use Gll\Hub\Contracts\Source\SourceDefinition;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** The instances whose turn has come: enabled, past their interval, past any wait they asked for. */
final class DueInstances
{
    public function __construct(private readonly PackageRegistry $registry)
    {
    }

    /** @return Collection<int, SourceInstance> */
    public function at(Carbon $now): Collection
    {
        return SourceInstance::enabled()
            // Failing means a credential or setting needs the admin: asking again would only repeat the error.
            ->where('status', '!=', SyncStatus::Failing->value)
            ->where(fn ($q) => $q->whereNull('retry_after')->orWhere('retry_after', '<=', $now))
            ->get()
            ->filter(function (SourceInstance $instance) use ($now): bool {
                $interval = $instance->interval_minutes ?? $this->defaultInterval($instance);

                return $interval !== null
                    && $interval > 0
                    && ($instance->last_run_at === null || $instance->last_run_at->copy()->addMinutes($interval)->lte($now));
            })
            ->values();
    }

    private function defaultInterval(SourceInstance $instance): ?int
    {
        $definition = $this->registry->source($instance->package, $instance->source_key)?->definition;

        return $definition instanceof SourceDefinition ? $definition->defaultIntervalMinutes : null;
    }
}
