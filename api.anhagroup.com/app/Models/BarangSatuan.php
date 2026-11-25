<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;


class BarangSatuan extends Model
{
    use SoftDeletes;

    protected $table = 'barangs_satuan';

    protected $fillable = [
        'nama'
    ];

    public function barangs(): HasMany
    {
        return $this->hasMany(Barang::class, 'satuan', 'id');
    }
}