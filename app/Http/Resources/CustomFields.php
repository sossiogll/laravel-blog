<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomFields extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'category_id' => $this->category_id,
            'custom_fields_values' => $this->custom_fields_values,
        ];
    }
}
