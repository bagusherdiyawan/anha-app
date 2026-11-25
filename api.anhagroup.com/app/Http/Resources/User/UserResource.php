<?php

namespace App\Http\Resources\User;

use App\Http\Resources\AlamatPengiriman\AlamatPengirimanResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'role' => $this->role,
            'kode_grosir' => $this->kode_grosir,
            'photo_url' => $this->photo_url,
            'photo_full_url' => $this->photo_full_url,
            'email_verified_at' => $this->email_verified_at,
            'is_active' => $this->is_active,
            'alamats' => AlamatPengirimanResource::collection($this->whenLoaded('alamats')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
