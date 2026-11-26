<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Http\Resources\BarangResource;
use App\Http\Resources\BarangPerKategoriResource;
use App\Models\Barang;
use App\Models\BarangFoto;
use App\Models\Kategori_barang;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangFrontController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->tglNow = date('Y-m-d');
    }
    private function getKolHarga($role)
    {
        if ($role == 'ritel') {
            return 'barangs_hrg_ritel.harga as harga';
        } else if ($role == 'grosir') {
            return 'barangs_hrg_grosir.harga as harga';
        }
    }
    private function validasi_role($role)
    {
        if (empty($role)) {
            return $this->errorResponse(
                'Role tidak ditemukan',
                404
            );
        }
    }
    private function setHargaJoin($role, $barang)
    {
        if ($role == 'ritel') {
            return $barang->join('barangs_hrg_ritel', 'barangs_hrg_ritel.id_barang', '=', 'barangs.id');
        } else if ($role == 'grosir') {
            return $barang->join('barangs_hrg_grosir', 'barangs_hrg_grosir.id_barang', '=', 'barangs.id');
        }
    }
    private function getStatusAktif($role, $barang)
    {
        if ($role == 'ritel') {
            return $barang->where('barangs_hrg_ritel.is_active', '1');
        } else if ($role == 'grosir') {
            return $barang->where('barangs_hrg_grosir.is_active', '1');
        }
    }

    private function baseProductQuery(string $role)
    {
        $kolharga = $this->getKolHarga($role);
        $barang = Barang::select(
            'barangs.id',
            'kode_barang as code',
            'nama_barang as name',
            DB::raw("
                CONCAT(
                    LOWER(
                        REPLACE(
                            REPLACE(
                                REPLACE(nama_barang, ' ', '-'),
                                '.', ''
                            ),
                            '/', '-'
                        )
                    ),
                    '-',
                    barangs.id
                ) as slug
            "),
            'barcode',
            'satuan',
            $kolharga,
            'deskripsi as description',
            'rating as quality_rating',
            'stock',
            'is_available',
            DB::raw('(select url_foto_barang from barangs_foto where id_barang = barangs.id limit 1) as primary_image_url'),
            'kode_kategori',
        )->with([
            'category:id,kode,nama as name',
            'satuanBarang' => fn ($q) => $q->withTrashed()->select('id', 'nama'),
            'fotos:id,id_barang,url_foto_barang',
        ]);

        $barang = $this->setHargaJoin($role, $barang);
        return $this->getStatusAktif($role, $barang);
    }

    public function landing(Request $request)
    {
        $role = $request->user()->role;
        $validasi = $this->validasi_role($role);
        if ($validasi) {
            return $validasi;
        }

        try {
            $barang = $this->baseProductQuery($role)
                ->limit(10)
                ->orderBy('barangs.id', 'desc');

            if ($barang->exists()) {
                $barang = $barang->get();

                return [
                    'data' => $barang->map(function ($item) {
                        $satuanNama = $item->resolvedSatuanName();

                        return [
                            'id' => $item->id,
                            'code' => $item->code,
                            'name' => $item->name,
                            'slug' => $item->slug,
                            'description' => $item->description,
                            'satuan' => $satuanNama,
                            'satuan_nama' => $satuanNama,
                            'category' => [
                                'id' => $item->category?->id,
                                'name' => $item->category?->name,
                            ],
                            'primary_image_url' => $item->primary_image_url,
                            'images' => $item->primary_image_url,
                            'fotos' => $item->fotoUrls(),
                            'retail_price' => (int) $item->harga,
                            'price' => (int) $item->harga,
                            'stock' => (int) $item->stock,
                            'is_available' => (bool) $item->is_available && (int) $item->stock > 0,
                            'quality_rating' => (int) $item->quality_rating,
                        ];
                    })
                ];
            } else {
                return $this->errorResponse(
                    'Barang gagal ditemukan',
                    404
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan menghubungkan ke server',
                500
            );
        }
    }
    public function show(Request $request, $id)
    {
        $role = $request->user()->role;
        $validasi = $this->validasi_role($role);
        if ($validasi) {
            return $validasi;
        }

        try {
            $barang = $this->baseProductQuery($role)
                ->where(['barangs.id' => $id]);

            if ($barang->exists()) {
                $barang = $barang->first();
                $satuanNama = $barang->resolvedSatuanName();

                return [
                    'data' => [
                        'id' => $barang->id,
                        'code' => $barang->code,
                        'name' => $barang->name,
                        'slug' => $barang->slug,
                        'description' => $barang->description,
                        'satuan' => $satuanNama,
                        'satuan_nama' => $satuanNama,
                        'category' => [
                            'id' => $barang->category?->id,
                            'name' => $barang->category?->name ?? $barang->category?->nama,
                        ],
                        'primary_image_url' => $barang->primary_image_url,
                        'images' => BarangFoto::getFotoByIdBarang($id),
                        'fotos' => $barang->fotoUrls(),
                        'retail_price' => (int) $barang->harga,
                        'price' => (int) $barang->harga,
                        'stock' => (int) $barang->stock,
                        'is_available' => (bool) $barang->is_available && (int) $barang->stock > 0,
                        'quality_rating' => (int) $barang->quality_rating,
                    ]
                ];
            } else {
                return $this->errorResponse(
                    'Barang gagal ditemukan',
                    404
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan menghubungkan ke server',
                500
            );
        }
    }
    public function showByKategoriId($id, Request $request)
    {
        $semua = $request->query('semua');
        $hargaup = $request->query('hargaup');
        $hargadown = $request->query('hargadown');
        $stokready = $request->query('stokready');
        $terlaris = $request->query('terlaris');
        $role = $request->user()->role;

        $validasi = $this->validasi_role($role);
        if ($validasi) {
            return $validasi;
        }

        $kolharga = $this->getKolHarga($role);

        try {
            $barang = $this->baseProductQuery($role);

            $kategoriModel = Kategori_barang::where('id', $id)
                ->orWhere('kode', $id)
                ->orWhere('nama', 'like', "%{$id}%")
                ->first();
            $kodeKategori = $kategoriModel?->kode ?? $id;
            $barang = $barang->where('barangs.kode_kategori', $kodeKategori);

            if ($hargaup == '1') {
                $barang = $barang->orderBy($kolharga, 'asc');
            } else if ($hargadown == '1') {
                $barang = $barang->orderBy($kolharga, 'desc');
            } else if ($terlaris == '1') {
                $barang = $barang->orderBy('id', 'desc');
            } else if ($semua == '1') {
                $barang = $barang;
            }

            $limit = (int) $request->query('limit', $request->query('per_page', 100));
            if ($limit <= 0 || $limit > 250) {
                $limit = 100;
            }

            if ($stokready == '1') {
                $barang = $barang->where('barangs.stock', '>', 0);
            }

            $paginated = $barang->paginate($limit);
            return BarangPerKategoriResource::collection($paginated);
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan menghubungkan ke server',
                500
            );
        }
    }
    public function showByBarcode($code)
    {
        try {
            $barang = Barang::with([
                'hargaRitel',
                'hargaGrosir',
                'satuanBarang' => fn ($q) => $q->withTrashed()->select('id', 'nama'),
                'fotos:id,id_barang,url_foto_barang',
            ])
                ->where('barcode', $code)
                ->where('is_available', 1)
                ->first();

            if ($barang) {
                $satuanNama = $barang->resolvedSatuanName();
                $barang->setAttribute('satuan_nama', $satuanNama);
                $barang->setAttribute('satuan', $satuanNama);

                return $this->successResponse(
                    ['barang' => $barang, 'foto' => BarangFoto::getFotoByIdBarang($barang->id)],
                    'Barang berdasarkan barcode berhasil ditemukan'
                );
            } else {
                return $this->errorResponse(
                    'Barang gagal ditemukan',
                    404
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan menghubungkan ke server',
                500
            );
        }
    }
    public function search(Request $request)
    {
        try {
            $keyword = $request->query('q');
            $kodebarang = $request->query('cb');
            $role = $request->user()->role;

            $validasi = $this->validasi_role($role);
            if ($validasi) {
                return $validasi;
            }

            $barangs = $this->baseProductQuery($role);

            if (!empty($keyword)) {
                $barangs = $barangs->where(function ($q) use ($keyword) {
                    $q->where('nama_barang', 'like', '%' . $keyword . '%')
                        ->orWhere('kode_barang', 'like', '%' . $keyword . '%')
                        ->orWhere('barcode', 'like', '%' . $keyword . '%');
                });
            }
            if (!empty($kodebarang)) {
                $barangs = $barangs->where('kode_barang', $kodebarang);
            }

            $barangs = $barangs->orderBy('nama_barang', 'asc');

            if ($barangs->exists()) {
                return BarangResource::collection(
                    $barangs->paginate(10)
                );
            } else {
                return $this->errorResponse(
                    'Barang gagal ditemukan',
                    404
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan menghubungkan ke server',
                500
            );
        }
    }
}
