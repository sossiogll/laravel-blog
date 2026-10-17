<?php

declare(strict_types=1);

namespace App\Sync;

use App\Models\SourceInstance;
use App\Models\SyncStatus;
use Illuminate\Support\Carbon;

/** Writes down how a run went on the instance itself, where the admin and the scheduler read it. */
final class InstanceHealth
{
    public function started(SourceInstance $instance): void
    {
        $instance->forceFill(['last_run_at' => Carbon::now('UTC')])->save();
    }

    public function succeeded(SourceInstance $instance): void
    {
        $instance->forceFill([
            'status' => SyncStatus::Ok,
            'last_success_at' => Carbon::now('UTC'),
            'last_error' => null,
            'retry_after' => null,
        ])->save();
    }

    public function failed(SourceInstance $instance, Failure $failure): void
    {
        $instance->forceFill([
            'status' => $failure->status,
            'last_error' => $failure->message,
            'retry_after' => $failure->retryIn === null ? null : Carbon::now('UTC')->addSeconds($failure->retryIn),
        ])->save();
    }
}
