<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class Categories extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'post_count' => $this->posts()->count(),
            'custom_fields' => $this->custom_fields
        ];
    }
}
