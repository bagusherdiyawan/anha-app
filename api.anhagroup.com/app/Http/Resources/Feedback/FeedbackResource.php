<?php

namespace App\Http\Resources\Feedback;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class FeedbackResource extends JsonResource
{
    /**
     * Transform resource
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
            'category' => $this->category,
            'content' => $this->content,
            'photo_paths' => $this->photo_paths,
            'photo_urls' => $this->photo_urls,
            'status' => $this->status,
            'replies' => FeedbackReplyResource::collection(
                $this->whenLoaded('replies')
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
