<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    protected $fillable = [
        'user_id',
        'reward_id',
        'points_spent',
        'claim_code',
        'status',
        'confirmed_by',
        'claimed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'points_spent' => 'integer',
            'claimed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function confirmedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function voucher(): HasOne
    {
        return $this->hasOne(Voucher::class, 'reward_redemption_id');
    }
}
