<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Generate_kode_grosir extends Model
{
    protected $table = 'generate_kode_grosir';

    protected $fillable = [
        'kode_grosir',
    ];

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Relasi user berdasarkan kode_grosir
     */
    public function user()
    {
        return $this->hasOne(
            User::class,
            'kode_grosir',
            'kode_grosir'
        );
    }

    /**
     * Attribute apakah sudah digunakan
     */
    public function getIsUsedAttribute(): bool
    {
        return $this->user()->exists();
    }
}
