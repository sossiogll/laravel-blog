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
        'custom_fields',
        'updated_at'
    ];

    protected $hidden = ['custom_fields'];

    public $appends = ['fields'];
    
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

    public function postCustomFields(): belongsToMany{
        return $this->belongsToMany(Users::class)->withPivot('custom_fields');
    }

    public function getFieldsAttribute()
    {
        $fields = array();

        $jsonFields = $this->custom_fields;
        $fields_temp = json_decode($jsonFields, true);

        if($fields_temp != null)
            foreach(array_keys($fields_temp) as $key){

                $field_temp = array();
                $field_temp["id"] = $key;
                $field_temp["description"] = $fields_temp[$key];
                array_push($fields, $field_temp);
            };
        return $fields;
    }

    public function areCustomFieldsEditable(){
        return $this->posts()->count()==0;
    }


    

}
