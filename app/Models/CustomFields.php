<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CustomFields extends Model
{
    use HasFactory;

    protected $table = "category_post";

    protected $fillable = [
        'post_id',
        'category_id',
        'raw_custom_fields_values'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public $appends = [
        'custom_fields_values'
    ];

 

    /**
     * Return the post category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Return the post category
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function getCustomFieldsValuesAttribute(){
    
        return json_decode($this->attributes['raw_custom_fields_values'], true);
    }

}
