<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_barang',
        'kode_barang',
        'barcode',
        'satuan',
        'deskripsi',
        'kode_kategori',
        'rating',
        'stock',
        'is_available',
    ];

    protected $casts = [
        'stock' => 'integer',
        'is_available' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'kode_kategori',
    ];

    public function satuanBarang(): BelongsTo
    {
        return $this->belongsTo(BarangSatuan::class, 'satuan', 'id');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(BarangFoto::class, 'id_barang', 'id');
    }

    /**
     * Resolve displayable unit name even when `barangs.satuan` stores a foreign-key ID.
     */
    public function resolvedSatuanName(string $fallback = 'pcs'): string
    {
        $joined = trim((string) ($this->getAttribute('satuan_nama') ?? ''));
        if ($joined !== '') {
            return $joined;
        }

        if ($this->relationLoaded('satuanBarang')) {
            $relName = trim((string) ($this->satuanBarang?->nama ?? ''));
            if ($relName !== '') {
                return $relName;
            }
        }

        $raw = trim((string) ($this->getAttribute('satuan') ?? ''));
        if ($raw !== '' && !ctype_digit($raw)) {
            return $raw;
        }

        if ($raw !== '' && ctype_digit($raw)) {
            $unit = BarangSatuan::withTrashed()->select('id', 'nama')->find((int) $raw);
            $unitName = trim((string) ($unit?->nama ?? ''));
            if ($unitName !== '') {
                return $unitName;
            }
        }

        return $fallback;
    }

    /**
     * Return deduplicated product photo URLs in display order.
     */
    public function fotoUrls(): array
    {
        if ($this->relationLoaded('fotos')) {
            $urls = $this->fotos->pluck('url_foto_barang')->filter()->values()->all();
            if (!empty($urls)) {
                return array_values(array_unique($urls));
            }
        }

        $primary = trim((string) ($this->getAttribute('primary_image_url') ?? ''));
        if ($primary !== '') {
            $resolved = str_starts_with($primary, 'http') ? $primary : asset('storage/' . ltrim($primary, '/'));
            return [$resolved];
        }

        return [];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Kategori_barang::class, 'kode_kategori', 'kode');
    }

    public function hargaRitel()
    {
        return $this->hasOne(HargaRitel::class, 'id_barang');
    }

    public function hargaGrosir()
    {
        return $this->hasOne(HargaGrosir::class, 'id_barang');
    }

    /**
     * Resolve the active unit price for a given buyer role, or null when inactive/missing.
     */
    public function activePriceForRole(?string $role): ?int
    {
        if ($role === 'grosir') {
            return $this->hargaGrosir?->is_active ? (int) $this->hargaGrosir->harga : null;
        }
        if ($role === 'ritel') {
            return $this->hargaRitel?->is_active ? (int) $this->hargaRitel->harga : null;
        }

        return null;
    }

    protected function foto(): Attribute
    {
        return Attribute::make(
            get: fn($foto) => $foto ? asset('storage/' . $foto) : null,
        );
    }

    static function data_barang($id)
    {
        return Barang::join('barangs_hrg_ritel', 'barangs_hrg_ritel.id_barang', '=', 'barangs.id')
            ->join('barangs_hrg_grosir', 'barangs_hrg_grosir.id_barang', '=', 'barangs.id')
            ->join('kategori_barangs', 'kategori_barangs.kode', '=', 'barangs.kode_kategori')
            ->join('barangs_satuan', 'barangs_satuan.id', '=', 'barangs.satuan')
            ->select(
                'barangs.id as id',
                'barangs.nama_barang as nama_barang',
                'barangs.kode_barang as kode_barang',
                'barangs.barcode as barcode',
                'barangs.satuan as satuan',
                'barangs_satuan.nama as satuan_nama',
                'barangs.deskripsi as deskripsi',
                'barangs.kode_kategori as kode_kategori',
                'barangs.rating as rating',
                'barangs.stock as stock',
                'barangs.is_available as is_available',
                'kategori_barangs.nama as nama_kategori',
                'barangs_hrg_ritel.harga as harga_ritel',
                'barangs_hrg_grosir.harga as harga_grosir',
                'barangs_hrg_ritel.is_active as status_ritel',
                'barangs_hrg_grosir.is_active as status_grosir',
            )
            ->where('barangs.id', $id)
            ->first();
    }

    public function keranjangs()
    {
        return $this->hasMany(Keranjang::class);
    }
}
