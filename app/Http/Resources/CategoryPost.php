<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class CategoryPost extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary_content' => $this->summary_content,
            'posted_at' => $this->posted_at->toIso8601String(),
            'comments_count' => $this->comments_count ?? $this->comments()->count(),
            'thumbnail_url' => $this->when($this->hasThumbnail(), url(optional($this->thumbnail)->getUrl(''))),
            'thumbnail_name' => $this->when($this->hasThumbnail(), optional($this->thumbnail)->name),
            'thumbnail_description' => $this->when($this->hasThumbnail(), optional($this->thumbnail)->description),
            'custom_fields_values' => $this->custom_fields_values
        ];
    }
}
