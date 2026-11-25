<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Barang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori_barang extends Model 
{
    use SoftDeletes;
    
    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'foto',
        'landing',
        'status_active'
    ];

    public function barangs(): HasMany
    {
        return $this->hasMany(Barang::class, 'kode_kategori', 'kode');
    }

    protected function foto(): Attribute
    {
        return Attribute::make(
            get: fn($foto) => $foto ? (str_starts_with($foto, 'http') ? $foto : asset('storage/' . ltrim($foto, '/'))) : null,
        );
    }

    static function jmlProdukByKategori($kode_kategori)
    {
        $user = Auth::user();
        $role = $user?->role ?? 'ritel';

        $query = Barang::query();
        if ($role === 'grosir') {
            $query->join('barangs_hrg_grosir', 'barangs_hrg_grosir.id_barang', '=', 'barangs.id')
                  ->where('barangs_hrg_grosir.is_active', 1);
        } else {
            $query->join('barangs_hrg_ritel', 'barangs_hrg_ritel.id_barang', '=', 'barangs.id')
                  ->where('barangs_hrg_ritel.is_active', 1);
        }

        return $query->where('barangs.kode_kategori', $kode_kategori)->count();
    }
}
