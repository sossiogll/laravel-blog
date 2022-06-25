<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
            'positions' => $this->positions,
            'registered_at' => $this->registered_at->toIso8601String(),
            'comments_count' => $this->comments_count ?? $this->comments()->count(),
            'posts_count' => $this->posts_count ?? $this->posts()->count(),
            'roles' => Role::collection($this->roles),
            'profile_picture' => $this->when($this->hasProfilePicture(),
            [
                'url' => url(optional($this->profilePicture)->getUrl()),
                'name' => optional($this->profilePicture)->name,
                'description' => optional($this->profilePicture)->description
            ]),
            'secondary_profile_picture' => $this->when($this->hasSecondaryProfilePicture(),
                [
                    'url' => url(optional($this->secondaryProfilePicture)->getUrl()),
                    'name' => optional($this->secondaryProfilePicture)->name,
                    'description' => optional($this->secondaryProfilePicture)->description
                ])
        ];
    }
}
