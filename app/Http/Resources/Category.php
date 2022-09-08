<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryPost as PostResource;
use App\Models\Settings;



class Category extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $settings = Settings::first();
        
        if(Settings::first()->localization){
    
            if($request->has('language'))
                $language = $request->language;
            else
                $language = "it";

            return [
                'id' => $this->id,
                'slug' => $this->slug,
                'name' => $this->name,
                'post_count' => $this->posts()->where('language', $language)->count(),
                'custom_fields' => $this->custom_fields,
                'posts' => PostResource::collection(
                    $this->posts()
                        ->where('language', $language)
                        ->latest()
                        ->paginate(
                            $request->input('limit', 20)
                        )
                ),
            ];
        }
        else
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
