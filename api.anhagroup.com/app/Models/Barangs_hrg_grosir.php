<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Barangs_hrg_grosir extends Model
{
    protected $fillable = [
        'id_barang',
        'harga',
        'is_active',
    ];

    protected $table = 'barangs_hrg_grosir';

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id');
    }
}
