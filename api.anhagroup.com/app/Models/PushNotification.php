<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotification extends Model
{
    protected $fillable = ['user_id', 'category', 'title', 'body', 'data', 'image', 'action_url', 'is_read', 'read_at'];

    protected $appends = ['image_url'];

    protected $hidden = ['image'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function getImageUrlAttribute()
    {
        return $this->image
            ? asset('storage/'.$this->image)
            : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
