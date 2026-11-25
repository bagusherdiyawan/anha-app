<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori_barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Kategori_barangController extends Controller
{
    public function index(Request $request)
    {
        try {
            $limit = $request->query('limit');
            $keyword = $request->query('q');

            if ($limit == 0) {
                $limit = 1000000;
            }

            $query = Kategori_barang::withCount('barangs')
                ->when($keyword, function ($q) use ($keyword) {
                    $q->where(function ($sub) use ($keyword) {
                        $sub->where('nama', 'like', '%' . $keyword . '%')
                            ->orWhere('kode', 'like', '%' . $keyword . '%')
                            ->orWhere('deskripsi', 'like', '%' . $keyword . '%');
                    });
                })
                ->when($request->filled('status_active'), function ($q) use ($request) {
                    $q->where('status_active', $request->query('status_active'));
                })
                ->orderBy('landing', 'desc')
                ->orderBy('id', 'desc');

            return response()->json([
                'success' => true,
                'message' => 'kategori barang berhasil ditemukan',
                'data' => $query->paginate($limit)
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
            'kode' => 'required|string|unique:kategori_barangs,kode',
            'nama' => 'required|string|max:255|unique:kategori_barangs,nama',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status_active' => 'nullable|boolean',
            'landing' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai ' . $validator->errors()
            ], 400);
        }

        if ($request->landing == '1') {
            $cekJmlLanding = Kategori_barang::where(['status_active' => 1, 'landing' => 1])->count();
            if ($cekJmlLanding > 7) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah landing kategori yang diusulkan tidak boleh lebih dari 8 buah',
                ], 404);
            }
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('photos_kategori', 'public');
        }

        $kategori = Kategori_barang::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'foto' => $fotoPath,
            'status_active' => $request->status_active ?? true,
            'landing' => $request->landing ?? true,
        ]);

        try {
            if ($kategori) {
                return response()->json([
                    'success' => true,
                    'message' => 'kategori berhasil ditambahkan',
                    'data' => $kategori
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'kategori gagal ditambahkan',
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
            $kategori = Kategori_barang::find($id);
            if ($kategori) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kategori barang berhasil ditemukan',
                    'data' => $kategori
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori barang gagal ditemukan',
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
        $kategori = Kategori_barang::find($id);
        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Detail kategori barang gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode' => ['required', Rule::unique('kategori_barangs', 'kode')->ignore($kategori->id)],
            'nama' => ['required', Rule::unique('kategori_barangs', 'nama')->ignore($kategori->id)],
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'landing' => 'nullable|boolean',
            'status_active' => 'nullable|boolean',
        ]);

        if ($request->landing == '1') {
            $cekJmlLanding = Kategori_barang::where(['status_active' => 1, 'landing' => 1])->where('id','!=',$kategori->id)->count();
            if ($cekJmlLanding > 7) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah landing kategori yang diusulkan tidak boleh lebih dari 8 buah',
                ], 404);
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai ' . $validator->errors()
            ], 400);
        }

        $rawFoto = $kategori->getRawOriginal('foto');
        $foto_root = (is_string($rawFoto) && str_contains($rawFoto, 'photos_kategori'))
            ? substr($rawFoto, strpos($rawFoto, 'photos_kategori'))
            : ($rawFoto ?: null);
        $fotoPath = $foto_root;

        if ($request->hasFile('foto')) {
            if ($foto_root && Storage::disk('public')->exists($foto_root)) {
                Storage::disk('public')->delete($foto_root);
            }
            $fotoPath = $request->file('foto')->store('photos_kategori', 'public');
        } elseif ($request->input('clear_foto') == 'ya') {
            if ($foto_root && Storage::disk('public')->exists($foto_root)) {
                Storage::disk('public')->delete($foto_root);
            }
            $fotoPath = null;
        }

        $update = $kategori->update([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'foto' => $fotoPath,
            'landing' => $request->landing ?? $kategori->landing,
            'status_active' => $request->status_active ?? $kategori->status_active,
        ]);

        try {
            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kategori barang berhasil diubah',
                    'data' => Kategori_barang::find($id),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori barang gagal diubah',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }

    public function destroy($id)
    {
        $kategori = Kategori_barang::find($id);
        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Detail kategori barang gagal ditemukan',
            ], 404);
        }

        try {
            $rawFoto = $kategori->getRawOriginal('foto');
            $foto_root = (is_string($rawFoto) && str_contains($rawFoto, 'photos_kategori'))
                ? substr($rawFoto, strpos($rawFoto, 'photos_kategori'))
                : ($rawFoto ?: null);

            if ($foto_root && Storage::disk('public')->exists($foto_root)) {
                Storage::disk('public')->delete($foto_root);
            }

            $delete = $kategori->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kategori barang berhasil dihapus',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori barang gagal dihapus',
                ], 500);
            }
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }
}
