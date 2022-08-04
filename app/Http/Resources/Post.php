<?php

namespace App\Http\Resources;
use App\Http\Resources\Media as MediaResource;
use Illuminate\Http\Resources\Json\JsonResource;


class Post extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'content_resume' => mb_strimwidth($this->content, 0, 90, "..."),
            'posted_at' => $this->posted_at->toIso8601String(),
            'comments_count' => $this->comments_count ?? $this->comments()->count(),
            'thumbnail_url' => $this->when($this->hasThumbnail(), url(optional($this->thumbnail)->getUrl())),
            'thumb_thumbnail_url' => $this->when($this->hasThumbnail(), url(optional($this->thumbnail)->getUrl('thumb'))),
            'thumbnail_name' => $this->when($this->hasThumbnail(), optional($this->thumbnail)->name),
            'thumbnail_description' => $this->when($this->hasThumbnail(), optional($this->thumbnail)->description),
            'category_slug' => $this->category->slug,
            'custom_fields_values' => $this->custom_fields_values,
            'carousel' => MediaResource::collection($this->carousel)
        ];
    }
}
