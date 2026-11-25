<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Rekening_toko extends Model
{
    protected $fillable = [
        'nama',
        'nomor',
        'type',
        'foto',
        'keterangan',
        'status_active',
    ];

    protected function foto(): Attribute
    {
        return Attribute::make(
            get: fn($foto) => $foto ? asset('storage/' . $foto) : null,
        );
    }
}
