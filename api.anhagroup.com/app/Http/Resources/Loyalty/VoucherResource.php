<?php

namespace App\Http\Resources\Loyalty;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class VoucherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
