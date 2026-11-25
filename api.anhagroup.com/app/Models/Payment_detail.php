<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment_detail extends Model
{
    protected $table = 'payment_details';

    protected $fillable = [
        'payment_id',
        'barang_id',
        'qyt',
        'subtotal'
    ];
}