<?php

namespace App\Http\Resources\Feedback;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class FeedbackReplyResource extends JsonResource
{
    /**
     * Transform resource
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'admin' => [
                'id' => $this->admin?->id,
                'name' => $this->admin?->name,
                'email' => $this->admin?->email,
            ],
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
