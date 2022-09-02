<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryPost as PostResource;


class Category extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'post_count' => $this->posts()->count(),
            'custom_fields' => $this->custom_fields,
            'posts' => PostResource::collection(
                $this->posts()
                     ->latest()
                     ->paginate(
                        $request->input('limit', 20)
                     )
            ),
        ];
    }
}
