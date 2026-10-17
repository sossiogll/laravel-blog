<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One configuration of a source: this repository, that feed. Its credentials are
 * encrypted at rest, decrypted only when a context asks for them, and never part
 * of an array or JSON form of the model.
 *
 * @property array<string, mixed> $settings
 * @property array<string, string>|null $credentials
 */
class SourceInstance extends Model
{
    protected $fillable = [
        'package', 'source_key', 'label', 'settings', 'credentials', 'enabled', 'interval_minutes',
    ];

    protected $hidden = ['credentials'];

    protected $attributes = [
        'settings' => '[]',
        'enabled' => true,
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'credentials' => 'encrypted:array',
            'enabled' => 'boolean',
            'interval_minutes' => 'integer',
            'status' => SyncStatus::class,
            'last_run_at' => 'datetime',
            'last_success_at' => 'datetime',
            'retry_after' => 'datetime',
        ];
    }

    /** @return HasMany<Entry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    /**
     * @param Builder<self> $query
     * @return Builder<self>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}
