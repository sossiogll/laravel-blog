<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Category extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'created_at',
        'updated_at'
    ];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

        /**
     * The "booting" method of the model.
     */
    protected static function boot(): void
    {
        parent::boot();
        //static::addGlobalScope(new PostedScope);
    }

    /**
     * Prepare a date for array / JSON serialization.
     
    *protected function serializeDate(DateTimeInterface $date): string
    *{
    *    return $date->format('Y-m-d H:i:s');
    *}*/

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Scope a query to search posts
     */
    public function scopeSearch(Builder $query, ?string $search)
    {
        if ($search) {
            return $query->where('name', 'LIKE', "%{$search}%");
        }
    }

        
    /**
     * Scope a query to order posts by latest posted
     */
    public function scopeAlphabeticalOrder(Builder $query): Builder
    {
        return $query->orderBy('name', 'desc');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
    

}
