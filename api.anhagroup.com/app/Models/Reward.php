<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reward extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'barang_id',
        'name',
        'description',
        'image',
        'points_required',
        'stock',
        'discount_value',
        'discount_type',
        'max_discount',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'points_required' => 'integer',
            'stock' => 'integer',
            'discount_value' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function isPhysical(): bool
    {
        return $this->type === 'physical_item';
    }

    public function isVoucher(): bool
    {
        return $this->type === 'voucher_discount';
    }

    public function isDirect(): bool
    {
        return $this->type === 'direct_discount';
    }

    public function hasUnlimitedStock(): bool
    {
        return $this->stock === -1;
    }

    public function isAvailable(): bool
    {
        return $this->is_active &&
            ($this->hasUnlimitedStock() || $this->stock > 0) &&
            ($this->starts_at === null || $this->starts_at->isPast()) &&
            ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function resolvedName(): ?string
    {
        return $this->barang?->nama_barang ?? $this->name;
    }

    public function resolvedDescription(): ?string
    {
        return $this->barang?->deskripsi ?? $this->description;
    }

    public function resolvedImageUrl(): ?string
    {
        if ($this->barang_id) {
            $path = BarangFoto::where('id_barang', $this->barang_id)->value('url_foto_barang');
            return $path ? url('storage/' . $path) : null;
        }

        return $this->image ? url('storage/' . $this->image) : null;
    }
}
