<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'alamat_id',
        'biaya_antar_id',
        'barang_id',
        'total',
        'method_bayar',
        'rektoko_id',
        'status_bayar',
        'bukti_bayar',
    ];

    protected $casts = [
        'total' => 'integer',
    ];

    protected function bukti_bayar(): Attribute
    {
        return Attribute::make(
            get: fn($bukti_bayar) => $bukti_bayar ? asset('storage/' . $bukti_bayar) : null,
        );
    }
}
