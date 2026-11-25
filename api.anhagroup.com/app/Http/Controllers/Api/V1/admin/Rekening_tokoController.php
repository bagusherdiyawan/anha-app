<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RekeningToko\RekeningTokoResource;
use App\Models\Rekening_toko;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class Rekening_tokoController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $limit = $request->query('limit', 10);
            $keyword = $request->query('search');

            $rekenings = Rekening_toko::orderBy('id', 'desc');

            // search optional
            if ($keyword) {
                $rekenings
                    ->where(
                        'nama',
                        'like',
                        '%' . $keyword . '%'
                    )
                    ->orWhere('nomor', 'like', "%{$keyword}%")
                    ->orWhere('type', 'like', "%{$keyword}%")
                    ->orWhere('keterangan', 'like', "%{$keyword}%");
            }

            // paginate dulu
            $rekenings = $rekenings->paginate($limit);

            return RekeningTokoResource::collection($rekenings);
        } catch (Exception $e) {
            Log::error('Rekening_tokoController::index gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'nomor' => 'nullable|string',
            'type' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status_active' => 'nullable',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai: ' . $validator->errors()->first()
            ], 400);
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('photos_rekening', 'public');
        }

        $rekening = Rekening_toko::create([
            'nama' => $request->nama,
            'nomor' => $request->nomor ?? '-',
            'type' => $request->type ?? 'transfer',
            'foto' => $fotoPath,
            'keterangan' => $request->keterangan,
            'status_active' => $request->has('status_active') ? (int) $request->boolean('status_active') : 1,
        ]);

        try {
            if ($rekening) {
                return response()->json([
                    'success' => true,
                    'message' => 'rekening berhasil ditambahkan',
                    'data' => $rekening
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'rekening gagal ditambahkan',
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
            $rekening = Rekening_toko::find($id);
            if ($rekening) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rekening toko berhasil ditemukan',
                    'data' => $rekening
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekening toko gagal ditemukan',
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
        $rekening = Rekening_toko::find($id);
        if (!$rekening) {
            return response()->json([
                'success' => false,
                'message' => 'Detail rekening gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'nomor' => 'nullable|string',
            'type' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status_active' => 'nullable',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai: ' . $validator->errors()->first()
            ], 400);
        }

        $rawFoto = $rekening->getRawOriginal('foto');
        $fotoPath = $rawFoto;

        if ($request->hasFile('foto')) {
            if ($rawFoto && Storage::disk('public')->exists($rawFoto)) {
                Storage::disk('public')->delete($rawFoto);
            }
            $fotoPath = $request->file('foto')->store('photos_rekening', 'public');
        } elseif ($request->input('clear_foto') == 'ya') {
            if ($rawFoto && Storage::disk('public')->exists($rawFoto)) {
                Storage::disk('public')->delete($rawFoto);
            }
            $fotoPath = null;
        }

        $update = $rekening->update([
            'nama' => $request->nama,
            'nomor' => $request->nomor ?? '-',
            'type' => $request->type,
            'keterangan' => $request->keterangan,
            'foto' => $fotoPath,
            'status_active' => $request->has('status_active') ? (int) $request->boolean('status_active') : $rekening->status_active,
        ]);

        try {
            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rekening toko berhasil diubah',
                    'data' => Rekening_toko::find($id),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekening toko gagal diubah',
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
        $rekening = Rekening_toko::find($id);
        if (!$rekening) {
            return response()->json([
                'success' => false,
                'message' => 'Detail rekening gagal ditemukan',
            ], 404);
        }

        try {
            $rawFoto = $rekening->getRawOriginal('foto');

            if ($rawFoto && Storage::disk('public')->exists($rawFoto)) {
                Storage::disk('public')->delete($rawFoto);
            }

            $delete = $rekening->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rekening toko berhasil dihapus',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Rekening toko gagal dihapus',
                ], 500);
            }
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }

    public function toggleActive($id)
    {
        $rekening = Rekening_toko::find($id);
        if (!$rekening) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening toko tidak ditemukan',
            ], 404);
        }

        $rekening->status_active = !$rekening->status_active;
        $rekening->save();

        return response()->json([
            'success' => true,
            'message' => 'Status rekening berhasil diubah',
            'data' => $rekening,
        ]);
    }
}
