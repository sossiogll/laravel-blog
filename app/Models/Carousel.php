<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Carousel extends Model
{
    use HasFactory;

    protected $table = "media_post";

    protected $fillable = [
        'post_id',
        'media_id',
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
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * Return the post category
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

}
