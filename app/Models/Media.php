<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Media extends BaseMedia
{

  protected $fillable = [
    'description'
];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
      'posted_at'
  ];


  public function getDescriptionAttribute(){

    $description = "";

    if($this->hasCustomProperty("description"))
      $description = $this->getCustomProperty("description");

    return $description;

  }

  /**
  * Return the media's post
  */
  public function carousel(): HasMany
  {
    return $this->hasMany(Post::class);
  }

}
