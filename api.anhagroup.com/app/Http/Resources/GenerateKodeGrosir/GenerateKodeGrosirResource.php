<?php

namespace App\Http\Resources\GenerateKodeGrosir;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class GenerateKodeGrosirResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_grosir' => $this->kode_grosir,
            'is_used' => $this->is_used,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }
}
