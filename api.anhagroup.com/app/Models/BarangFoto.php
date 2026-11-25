<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BarangFoto extends Model
{
    protected $table = 'barangs_foto';

    protected $fillable = [
        'id_barang',
        'url_foto_barang'
    ];

    static function getFotoByIdBarang($id)
    {
        $foto = BarangFoto::select('url_foto_barang as foto_url')->where('id_barang', $id)->get();
        return $foto;
    }

    static function delFotoByIdBarang($id)
    {
        $fotos = BarangFoto::where('id_barang', $id)->get();
        foreach ($fotos as $foto) {
            $path = $foto->getRawOriginal('url_foto_barang');
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        BarangFoto::where('id_barang', $id)->delete();
    }

    public function foto(): Attribute
    {
        return Attribute::make(
            get: fn($foto) => $foto ? (str_starts_with($foto, 'http') ? $foto : asset('storage/' . ltrim($foto, '/'))) : null,
        );
    }

    public function fotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn($val) => $val ? (str_starts_with($val, 'http') ? $val : asset('storage/' . ltrim($val, '/'))) : null,
        );
    }

    public function urlFotoBarang(): Attribute
    {
        return Attribute::make(
            get: fn($val) => $val ? (str_starts_with($val, 'http') ? $val : asset('storage/' . ltrim($val, '/'))) : null,
        );
    }
}
