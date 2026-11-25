<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    protected $fillable = [
        'logo',
        'nama_toko',
        'alamat',
        'nomor_telepon',
    ];

    protected function logo(): Attribute
    {
        return Attribute::make(
            get: fn($foto) => $foto ? asset('storage/' . $foto) : null,
        );
    }
}
