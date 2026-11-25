<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'barang_id',
        'qty',
        'harga',
        'subtotal',
    ];

    protected $casts = [
        'harga' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
