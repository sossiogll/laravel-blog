<?php

declare(strict_types=1);

namespace App\Sync\Console;

use App\Sync\DueInstances;
use App\Sync\SyncSourceInstance;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

final class SyncDueCommand extends Command
{
    protected $signature = 'gllhub:sync-due';

    protected $description = 'Queue a sync for every source instance whose interval has elapsed';

    public function handle(DueInstances $due): int
    {
        $instances = $due->at(Carbon::now('UTC'));

        foreach ($instances as $instance) {
            SyncSourceInstance::dispatch($instance->id);
        }

        $this->info("Queued {$instances->count()} sync(s).");

        return self::SUCCESS;
    }
}
