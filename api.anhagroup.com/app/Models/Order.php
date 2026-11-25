<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'invoice',
        'total',
        'status',
        'alamat_pengiriman',
        'estimated_points',
        'metode_pembayaran_id',
        'voucher_id',
        'discount_amount',
        'shipping_cost',
    ];

    protected $casts = [
        'total' => 'integer',
        'estimated_points' => 'integer',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'integer',
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alamat_pengiriman()
    {
        return $this->belongsTo(Alamat::class, 'alamat_pengiriman', 'id');
    }

    public function metode_pembayaran()
    {
        return $this->belongsTo(Rekening_toko::class, 'metode_pembayaran_id', 'id');
    }

    static function alamatDefault(Request $request)
    {
        $alamatDefault = Alamat::select('id')->where('user_id', $request->user()->id)->where('default', 1)->first();
        return $alamatDefault?->id;
    }
}
