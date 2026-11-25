<?php

namespace App\Http\Resources\AlamatPengiriman;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class AlamatPengirimanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ],
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'phone' => $this->phone,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'district' => $this->district,
            'city' => $this->city,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
            'notes' => $this->notes,
            'default' => $this->default,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
