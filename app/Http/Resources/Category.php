<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Category extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'post_count' => $this->posts()->count(),
            'posts' => $this->posts()->latest()->paginate($request->input('limit', 20))
        ];
    }
}
