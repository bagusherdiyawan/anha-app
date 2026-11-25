<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriBarangResource;
use App\Models\Kategori_barang;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Kategori_barangFrontController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $search = $request->query('search', $request->query('q'));
            $limit = (int) $request->input('limit', $request->input('per_page', 100));
            if ($limit <= 0) {
                $limit = 100;
            }

            $kategoris = Kategori_barang::select(
                'id',
                'kode',
                'nama',
                'foto'
            )
            ->where('status_active', '1')
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', '%' . $search . '%');
            })
            ->orderBy('nama', 'asc')
            ->paginate($limit);

            $kategoris->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'kode' => $item->kode,
                    'name' => $item->nama,
                    'slug' => Str::slug($item->nama),
                    'icon' => $item->foto,
                    'jumlah_produk' => Kategori_barang::jmlProdukByKategori($item->kode),
                ];
            });

            if ($kategoris->count() > 0) {
                return KategoriBarangResource::collection($kategoris);
            } else {
                return $this->errorResponse(
                    'kategori barang gagal ditemukan',
                    404,
                    ['identifier' => 'kategori barang gagal ditemukan']
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan menghubungkan ke server',
                500,
                ['identifier' => 'Terjadi kesalahan menghubungkan ke server']
            );
        }
    }

    public function landing()
    {
        try {
            $kategoris = Kategori_barang::where('status_active', '1')->where('landing', 1)->orderBy('nama', 'asc')->get();
            $items = $kategoris->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'kode' => $item->kode,
                    'name' => $item->nama,
                    'slug' => Str::slug($item->nama),
                    'icon' => $item->foto,
                    'jumlah_produk' => Kategori_barang::jmlProdukByKategori($item->kode),
                ];
            });
            if ($items) {
                return $this->successResponse(
                    $items,
                    'kategori barang berhasil ditemukan'
                );
            } else {
                return $this->errorResponse(
                    'kategori barang gagal ditemukan',
                    404,
                    ['identifier' => 'kategori barang gagal ditemukan']
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan menghubungkan ke server',
                500,
                ['identifier' => 'Terjadi kesalahan menghubungkan ke server']
            );
        }
    }
}
