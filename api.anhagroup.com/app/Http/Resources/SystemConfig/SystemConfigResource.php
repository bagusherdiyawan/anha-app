<?php

namespace App\Http\Resources\SystemConfig;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class SystemConfigResource extends JsonResource
{
    /**
     * Transform resource
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $this->value,
            'parsed_value' => $this->parsed_value,
            'type' => $this->type,
            'group' => $this->group,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
