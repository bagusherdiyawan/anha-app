<?php

namespace App\Http\Resources\Loyalty;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class RewardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->resolvedName(),
            'description' => $this->resolvedDescription(),
            'image_url' => $this->resolvedImageUrl(),
            'points_required' => $this->points_required,
            'stock' => $this->stock,
            'discount_value' => $this->discount_value,
            'discount_type' => $this->discount_type,
            'max_discount' => $this->max_discount,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
        ];
    }
}
