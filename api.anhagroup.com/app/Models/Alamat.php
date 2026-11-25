<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alamat extends Model
{
    use SoftDeletes;

    protected $table = 'alamat_pengiriman';

    protected $fillable = [
        'user_id',
        'address',
        'label',
        'recipient_name',
        'phone',
        'district',
        'city',
        'province',
        'postal_code',
        'notes',
        'default',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'default' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->hasMany(Order::class);
    }
}