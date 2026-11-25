<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarangResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $resolvedSatuan = method_exists($this->resource, 'resolvedSatuanName')
            ? $this->resource->resolvedSatuanName()
            : ($data['satuan'] ?? 'pcs');

        $data['satuan'] = $resolvedSatuan;
        $data['satuan_nama'] = $resolvedSatuan;

        if (method_exists($this->resource, 'fotoUrls')) {
            $data['fotos'] = $this->resource->fotoUrls();
        }

        return $data;
    }
}
