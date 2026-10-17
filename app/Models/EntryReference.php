<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A link from an entry's Reference field to another entry. It keeps what the
 * source said (an external id, or a type and a slug) next to the entry it resolved
 * to, so a target that appears later is still found.
 */
class EntryReference extends Model
{
    public $timestamps = false;

    protected $fillable = ['entry_id', 'field', 'position', 'target_entry_id', 'external_id', 'type', 'slug'];

    /** @return BelongsTo<Entry, $this> */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    /** @return BelongsTo<Entry, $this> */
    public function target(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'target_entry_id');
    }

    /**
     * @param Builder<self> $query
     * @return Builder<self>
     */
    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereNull('target_entry_id');
    }
}
