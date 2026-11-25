<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'id_barang',
        'harga',
        'tgl_active',
        'tgl_non_active',
        'viewfe',
        'viewbe',
    ];
    protected $casts = [
        'harga' => 'integer',
    ];
}
