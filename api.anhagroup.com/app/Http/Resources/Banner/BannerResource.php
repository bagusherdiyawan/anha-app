<?php

namespace App\Http\Resources\Banner;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image_path' => $this->image_path,
            'image_url' => $this->image_url,
            'description' => $this->description,
            'deep_link' => $this->deep_link,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
