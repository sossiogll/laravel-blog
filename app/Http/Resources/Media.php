<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Media extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alt' => $this->description,
            'src' => url($this->getUrl()),
            'thumb_src' => url($this->getUrl('thumb')),
        ];
    }
}
