<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;



class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
        'registered_at',
        'api_token',
        'raw_positions_value',
        'bio',
        'authenticable',
        'profile_picture_id',
        'secondary_profile_picture_id'
    ];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'registered_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'raw_positions_value'
    ];

    protected $appended = [
        'positions'
    ];


    /******************** Relationship ********************/


    /**
     * Return the user's profile picture
     */
    public function profilePicture(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'profile_picture_id');
    }

    /**
     * Return the user's secondary profile picture
     */
    public function secondaryProfilePicture(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'secondary_profile_picture_id');
    }

    /**
     * Return the user's posts
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * Return the user's comments
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'author_id');
    }

    /**
     * Return the user's likes
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'author_id');
    }

    /**
     * Return the user's roles
     */
    public function roles(): belongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /******************** Getting/Setting attributes ********************/


    /**
     * Get the user's fullname titleized.
     */
    public function getFullnameAttribute(): string
    {
        return Str::title($this->name);
    }

    /**
     * Return the user's positions
     */
    public function getPositionsAttribute(){
        return explode( ',', $this->attributes['raw_positions_value'] );

    }


    /******************** Scopes ********************/

    /**
     * Scope a query to only include users registered last week.
     */
    public function scopeLastWeek(Builder $query): Builder
    {
        return $query->whereBetween('registered_at', [carbon('1 week ago'), now()])
                     ->latest();
    }

    /**
     * Scope a query to order users by latest registered.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('registered_at', 'desc');
    }

    /**
     * Scope a query to filter available author users.
     */
    public function scopeAuthors(Builder $query): Builder
    {
        return $query->whereHas('roles', function ($query) {
            $query->where('roles.name', Role::ROLE_ADMIN)
                  ->orWhere('roles.name', Role::ROLE_EDITOR);
        });
    }

    /******************** Checks ********************/


    /**
     * Check if the user can be an author
     */
    public function canBeAuthor(): bool
    {
        return $this->isAdmin() || $this->isEditor();
    }

    /**
     * Check if the user has a role
     */
    public function hasRole(string $role): bool
    {
        return $this->roles->where('name', $role)->isNotEmpty();
    }

    /**
     * Check if the user has role admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ROLE_ADMIN);
    }

    /**
     * Check if the user has role editor
     */
    public function isEditor(): bool
    {
        return $this->hasRole(Role::ROLE_EDITOR);
    }

    /**
     * Check if the user has role editor
     */
    public function isAuthenticable(): bool
    {
        return $this->authenticable;
    }

    /**
     * Check if this is current logged in user
     */
    public function isCurrentUser(){
        return ($this->id == Auth::user()->id);
    }

        /**
     * return true if the post has a thumbnail
     */
    public function hasProfilePicture(): bool
    {
        return filled($this->profile_picture_id);
    }

        /**
     * return true if the post has a thumbnail
     */
    public function hasSecondaryProfilePicture(): bool
    {
        return filled($this->secondary_profile_picture_id);
    }




}
