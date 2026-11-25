<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarangPerKategoriResource extends JsonResource
{
    public function toArray($request): array
    {
        $satuanNama = method_exists($this->resource, 'resolvedSatuanName')
            ? $this->resource->resolvedSatuanName()
            : ($this->satuan ?? 'pcs');

        return [

            'id' => $this->id,

            'code' => $this->code,

            'name' => $this->name,

            'slug' => $this->slug,

            'description' => $this->description,

            'satuan' => $satuanNama,

            'satuan_nama' => $satuanNama,

            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],

            'primary_image_url' => $this->primary_image_url,

            'fotos' => method_exists($this->resource, 'fotoUrls') ? $this->resource->fotoUrls() : [],

            'retail_price' => (int) $this->harga,

            'price' => (int) $this->harga,

            'stock' => (int) $this->stock,

            'is_available' => (bool) $this->is_available && (int) $this->stock > 0,

            'quality_rating' => (int) $this->quality_rating,
        ];
    }
}