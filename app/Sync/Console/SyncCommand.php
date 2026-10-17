<?php

declare(strict_types=1);

namespace App\Sync\Console;

use App\Models\SourceInstance;
use App\Sync\SyncSourceInstance;
use Illuminate\Console\Command;

final class SyncCommand extends Command
{
    protected $signature = 'gllhub:sync {instance? : The id of a source instance} {--all : Every enabled instance}';

    protected $description = 'Sync a source instance now, waiting for the result';

    public function handle(): int
    {
        $id = $this->argument('instance');

        if ($this->option('all')) {
            $instances = SourceInstance::enabled()->get();
        } elseif ($id !== null) {
            $instances = SourceInstance::whereKey($id)->get();
            if ($instances->isEmpty()) {
                $this->error("There is no source instance {$id}.");

                return self::FAILURE;
            }
        } else {
            $this->error('Name an instance, or use --all.');

            return self::FAILURE;
        }

        $failed = false;
        foreach ($instances as $instance) {
            SyncSourceInstance::dispatchSync($instance->id);
            $instance->refresh();

            $line = "#{$instance->id} {$instance->label}: {$instance->status->value}";
            if ($instance->status->value === 'ok') {
                $this->info($line);
            } else {
                $failed = true;
                $this->error($line . ($instance->last_error === null ? '' : " ({$instance->last_error})"));
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
