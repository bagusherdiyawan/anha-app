<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class FeedbackReply extends Model
{
    protected $fillable = ['feedback_id', 'admin_id', 'content'];

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
