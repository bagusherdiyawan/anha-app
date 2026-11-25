<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use SoftDeletes;

    protected $table = 'feedbacks';

    protected $fillable = ['user_id', 'category', 'content', 'photo_paths', 'status'];

    protected $casts = [
        'photo_paths' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(FeedbackReply::class);
    }

    protected function photoPaths(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $photos = is_array($value)
                    ? $value
                    : json_decode($value, true);

                return collect($photos)->map(function ($item) {
                    return asset('storage/' . $item);
                })->toArray();
            }
        );
    }
}
