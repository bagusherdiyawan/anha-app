<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'user_id',
        'reward_redemption_id',
        'code',
        'discount_value',
        'discount_type',
        'max_discount',
        'min_purchase',
        'is_used',
        'expires_at',
        'used_at',
        'used_in_order_id',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'is_used' => 'boolean',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal < $this->min_purchase) {
            return 0;
        }

        if ($this->discount_type === 'fixed') {
            return min((float) $this->discount_value, $subtotal);
        }

        $discount = $subtotal * ((float) $this->discount_value / 100);
        if ($this->max_discount) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return $discount;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function redemption(): BelongsTo
    {
        return $this->belongsTo(RewardRedemption::class, 'reward_redemption_id');
    }

    public function usedInOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'used_in_order_id');
    }
}
