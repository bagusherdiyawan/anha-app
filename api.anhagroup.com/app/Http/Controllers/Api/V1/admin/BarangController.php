<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\BarangFoto;
use App\Models\Barangs_hrg_grosir;
use App\Models\Barangs_hrg_ritel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        try {
            $limit = $request->query('limit');
            $keyword = $request->query('q');
            $kategori = $request->query('kategori');
            $status_active_ritel = $request->query('status_active_ritel');
            $status_active_grosir = $request->query('status_active_grosir');

            $barangs = Barang::select(
                'barangs.id',
                'nama_barang',
                'kode_barang',
                'barcode',
                'satuan',
                'barangs_satuan.nama as satuan_nama',
                'barangs.deskripsi',
                'barangs_hrg_ritel.harga as harga_ritel',
                'barangs_hrg_grosir.harga as harga_grosir',
                'barangs_hrg_ritel.is_active as status_ritel',
                'barangs_hrg_grosir.is_active as status_grosir',
                'kode_kategori',
                'kategori_barangs.nama as nama_kategori',
                'rating',
                'barangs.stock',
                'barangs.is_available',
                DB::raw('(select url_foto_barang from barangs_foto where id_barang = barangs.id limit 1) as foto')
            )
                ->where('nama_barang', 'like', '%' . $keyword . '%')
                ->orderBy('id', 'desc');

            $barangs->join('barangs_satuan', 'barangs_satuan.id', '=', 'barangs.satuan');
            $barangs->join('kategori_barangs', 'kategori_barangs.kode', '=', 'barangs.kode_kategori');
            $barangs->join('barangs_hrg_ritel', 'barangs_hrg_ritel.id_barang', '=', 'barangs.id');
            $barangs->join('barangs_hrg_grosir', 'barangs_hrg_grosir.id_barang', '=', 'barangs.id');

            if (!empty($kategori)) {
                $barangs->where('kode_kategori', $kategori);
            }

            // if (!empty($status_active_ritel) && !empty($status_active_grosir)) {
            //     $barangs
            //         ->where('barangs_hrg_ritel.is_active', $status_active_ritel)
            //         ->orWhere('barangs_hrg_grosir.is_active', $status_active_grosir);
            // }


            if (!empty($status_active_ritel)) {
                if ($status_active_ritel == 'true') {
                    $status_active_ritelThen = 1;
                }
                else if ($status_active_ritel == 'false') {
                    $status_active_ritelThen = 0;
                }
                $barangs->where('barangs_hrg_ritel.is_active', $status_active_ritelThen);
            }


            if (!empty($status_active_grosir)) {
                if ($status_active_grosir == 'true') {
                    $status_active_grosirThen = 1;
                }
                else if ($status_active_grosir == 'false') {
                    $status_active_grosirThen = 0;
                }
                $barangs->where('barangs_hrg_grosir.is_active', $status_active_grosirThen);
            }

            // exit();

            return response()->json([
                'success' => true,
                'message' => 'Barang ritel berhasil ditemukan',
                'data' => $barangs->paginate($limit),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255|unique:barangs,nama_barang',
            'kode_barang' => 'required|string|max:6|unique:barangs,kode_barang',
            'barcode' => 'nullable|string|unique:barangs,barcode',
            'kode_kategori' => 'required|string',
            'satuan' => 'nullable|string',
            'harga_ritel' => 'required|numeric',
            'harga_grosir' => 'required|numeric',
            'deskripsi' => 'nullable|string',
            'foto.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'rating' => 'nullable|numeric|max:5|min:0',
            'status_active_ritel' => 'nullable|boolean',
            'status_active_grosir' => 'nullable|boolean',
            'stock' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai ' . $validator->errors()
            ], 400);
        }

        $barang = Barang::create([
            'nama_barang' => $request->nama_barang,
            'kode_barang' => $request->kode_barang,
            'barcode' => $request->barcode,
            'kode_kategori' => $request->kode_kategori,
            'satuan' => $request->satuan,
            'deskripsi' => $request->deskripsi,
            'rating' => $request->rating,
            'stock' => $request->input('stock', 0),
            'is_available' => $request->boolean('is_available', true),
        ]);

        $barang_hrg_ritel = Barangs_hrg_ritel::create([
            'id_barang' => $barang->id,
            'harga' => $request->harga_ritel,
            'is_active' => $request->status_active_ritel ?? true,
        ]);

        $barang_hrg_grosir = Barangs_hrg_grosir::create([
            'id_barang' => $barang->id,
            'harga' => $request->harga_grosir,
            'is_active' => $request->status_active_grosir ?? true,
        ]);

        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $path = $foto->store('photos_barang', 'public');
                BarangFoto::create([
                    'id_barang' => $barang->id,
                    'url_foto_barang' => $path
                ]);
            }
        }

        try {
            if ($barang) {
                return response()->json([
                    'success' => true,
                    'message' => 'barang berhasil ditambahkan',
                    'data' => ['barang' => Barang::data_barang($barang->id), 'foto' => BarangFoto::getFotoByIdBarang($barang->id)]
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'barang gagal ditambahkan',
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $barang = Barang::data_barang($id);
            if ($barang) {
                return response()->json([
                    'success' => true,
                    'message' => 'Barang berhasil ditemukan',
                    'data' => ['barang' => $barang, 'foto' => BarangFoto::getFotoByIdBarang($barang->id)]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang gagal ditemukan',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Detail barang gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'kode_barang' => ['required', 'string', 'max:6', Rule::unique('barangs')->ignore($barang->id)],
            'kode_kategori' => 'required|string',
            'barcode' => ['nullable', 'string', Rule::unique('barangs')->ignore($barang->id)],
            'satuan' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'foto.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'harga_ritel' => 'required|numeric',
            'harga_grosir' => 'required|numeric',
            'rating' => 'nullable|numeric|max:5|min:0',
            'status_active_ritel' => 'nullable|boolean',
            'status_active_grosir' => 'nullable|boolean',
            'stock' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai ' . $validator->errors()
            ], 400);
        }

        if ($request->hasFile('foto')) {
            BarangFoto::delFotoByIdBarang($barang->id);
            foreach ($request->file('foto') as $foto) {
                $path = $foto->store('photos_barang', 'public');
                BarangFoto::create([
                    'id_barang' => $barang->id,
                    'url_foto_barang' => $path
                ]);
            }
        } elseif ($request->input('clear_foto') == 'ya') {
            BarangFoto::delFotoByIdBarang($barang->id);
        }

        $update = $barang->update([
            'nama_barang' => $request->nama_barang,
            'kode_barang' => $request->kode_barang,
            'barcode' => $request->barcode,
            'kode_kategori' => $request->kode_kategori,
            'satuan' => $request->satuan,
            'deskripsi' => $request->deskripsi,
            'rating' => $request->rating,
            'stock' => $request->input('stock', $barang->stock),
            'is_available' => $request->boolean('is_available', true),
        ]);

        $barang_hrg_ritel = Barangs_hrg_ritel::where('id_barang', $barang->id)->update([
            'harga' => $request->harga_ritel,
            'is_active' => $request->status_active_ritel ?? true,
        ]);

        $barang_hrg_grosir = Barangs_hrg_grosir::where('id_barang', $barang->id)->update([
            'harga' => $request->harga_grosir,
            'is_active' => $request->status_active_grosir ?? true,
        ]);

        try {
            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'Barang berhasil diubah',
                    'data' => ['barang' => Barang::data_barang($barang->id), 'foto' => BarangFoto::getFotoByIdBarang($barang->id)]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang gagal diubah',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }

    public function update_status(Request $request, $id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Detail barang gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status_active_ritel' => 'required|boolean',
            'status_active_grosir' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai ' . $validator->errors()
            ], 400);
        }

        $updateritel = Barangs_hrg_ritel::where('id_barang', $barang->id)->update([
            'is_active' => $request->status_active_ritel ?? true,
        ]);

        $updategrosir = Barangs_hrg_grosir::where('id_barang', $barang->id)->update([
            'is_active' => $request->status_active_grosir ?? true,
        ]);

        try {
            if ($updateritel && $updategrosir) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status barang berhasil diubah',
                    'data' => Barang::data_barang($barang->id)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Status barang gagal diubah',
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }

    public function update_availability(Request $request, $id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Detail barang gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'is_available' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai ' . $validator->errors()
            ], 400);
        }

        $barang->update(['is_available' => $request->boolean('is_available')]);

        return response()->json([
            'success' => true,
            'message' => 'Ketersediaan barang berhasil diubah',
            'data' => Barang::data_barang($barang->id)
        ]);
    }

    public function destroy($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Detail barang gagal ditemukan',
            ], 404);
        }

        try {
            BarangFoto::delFotoByIdBarang($barang->id);

            $delete = $barang->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'Barang berhasil dihapus',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang gagal dihapus',
                ], 500);
            }
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }

    public function getDataRitel(Request $request)
    {
        try {
            $limit = $request->query('limit');
            $keyword = $request->query('q');
            $kategori = $request->query('kategori');
            $status_active = $request->query('status_active');

            $barangs = Barang::select('barangs.id', 'nama_barang', 'kode_barang', 'barcode', 'satuan', 'barangs_satuan.nama as satuan_nama', 'barangs.deskripsi', 'barangs_hrg_ritel.harga', 'barangs_hrg_ritel.is_active', 'kode_kategori', 'kategori_barangs.nama as nama_kategori', 'rating', DB::raw('(select url_foto_barang from barangs_foto where id_barang = barangs.id limit 1) as foto'))
                ->where('nama_barang', 'like', '%' . $keyword . '%')
                ->orderBy('id', 'desc');

            $barangs->join('barangs_satuan', 'barangs_satuan.id', '=', 'barangs.satuan');
            $barangs->join('barangs_hrg_ritel', 'barangs_hrg_ritel.id_barang', '=', 'barangs.id')->join('kategori_barangs', 'kategori_barangs.kode', '=', 'barangs.kode_kategori');

            if (!empty($kategori)) {
                $barangs->where('kode_kategori', $kategori);
            }

            if (!empty($status_active)) {
                $barangs->where('barangs_hrg_ritel.is_active', $status_active);
            }

            if ($barangs->exists()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Barang ritel berhasil ditemukan',
                    'data' => $barangs->paginate($limit),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang ritel tidak ditemukan',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }

    public function getDataGrosir(Request $request)
    {
        try {
            $limit = $request->query('limit');
            $keyword = $request->query('q');
            $kategori = $request->query('kategori');
            $status_active = $request->query('status_active');

            $barangs = Barang::select('barangs.id', 'nama_barang', 'kode_barang', 'barcode', 'satuan', 'barangs_satuan.nama as satuan_nama', 'barangs.deskripsi', 'barangs_hrg_grosir.harga', 'barangs_hrg_grosir.is_active', 'kode_kategori', 'kategori_barangs.nama as nama_kategori', 'rating', DB::raw('(select url_foto_barang from barangs_foto where id_barang = barangs.id limit 1) as foto'))
                ->where('nama_barang', 'like', '%' . $keyword . '%')
                ->orderBy('id', 'desc');

            $barangs->join('barangs_satuan', 'barangs_satuan.id', '=', 'barangs.satuan');
            $barangs->join('barangs_hrg_grosir', 'barangs_hrg_grosir.id_barang', '=', 'barangs.id')->join('kategori_barangs', 'kategori_barangs.kode', '=', 'barangs.kode_kategori');


            if (!empty($kategori)) {
                $barangs->where('kode_kategori', $kategori);
            }

            if (!empty($status_active)) {
                $barangs->where('barangs_hrg_grosir.is_active', $status_active);
            }

            if ($barangs->exists()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Barang ritel berhasil ditemukan',
                    'data' => $barangs->paginate($limit),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang ritel tidak ditemukan',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }
}
