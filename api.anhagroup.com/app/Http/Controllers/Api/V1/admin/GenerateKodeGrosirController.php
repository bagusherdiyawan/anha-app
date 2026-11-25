<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenerateKodeGrosir\GenerateKodeGrosirResource;
use App\Models\Generate_kode_grosir;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerateKodeGrosirController extends Controller
{
    /**
     * List kode grosir
     */
    public function index(Request $request)
    {
        $query = Generate_kode_grosir::with('user');

        if ($request->search) {
            $query->where(
                'kode_grosir',
                'like',
                '%' . $request->search . '%'
            );
        }

        $data = $query
            ->latest('id')
            ->paginate($request->per_page ?? 10)
            ->appends($request->query());

        return GenerateKodeGrosirResource::collection($data);
    }

    /**
     * Store manual kode grosir
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_grosir' => 'required|string|max:8|unique:generate_kode_grosir,kode_grosir',
        ]);

        // cek apakah sudah dipakai user
        $usedInUsers = User::where(
            'kode_grosir',
            $validated['kode_grosir']
        )->exists();

        if ($usedInUsers) {
            return response()->json([
                'success' => false,
                'message' => 'Kode grosir sudah digunakan oleh user'
            ], 422);
        }

        $data = Generate_kode_grosir::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kode grosir berhasil dibuat',
            'data' => new GenerateKodeGrosirResource($data)
        ], 201);
    }

    /**
     * Generate kode otomatis
     */
    public function generateKode(): JsonResponse
    {
        do {
            $kode = strtoupper(substr(
                str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ123456789'),
                0,
                8
            ));

            // cek tabel generate
            $existsGenerate = Generate_kode_grosir::where(
                'kode_grosir',
                $kode
            )->exists();

            // cek tabel users
            $existsUser = User::where(
                'kode_grosir',
                $kode
            )->exists();
        } while ($existsGenerate || $existsUser);

        $data = Generate_kode_grosir::create([
            'kode_grosir' => $kode
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kode grosir berhasil digenerate',
            'data' => new GenerateKodeGrosirResource($data)
        ]);
    }

    /**
     * Detail kode grosir
     */
    public function show(string $id): JsonResponse
    {
        $data = Generate_kode_grosir::with('user')->find($id);
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Kode grosir tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kode grosir',
            'data' => new GenerateKodeGrosirResource($data)
        ]);
    }

    /**
     * Update kode grosir
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $data = Generate_kode_grosir::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Kode grosir tidak ditemukan'
            ], 404);
        }

        // cek apakah sedang dipakai user
        $usedInUsers = User::where(
            'kode_grosir',
            $data->kode_grosir
        )->exists();

        if ($usedInUsers) {
            return response()->json([
                'success' => false,
                'message' => 'Kode grosir sedang digunakan user dan tidak bisa diupdate'
            ], 422);
        }

        $validated = $request->validate([
            'kode_grosir' =>
                'sometimes|required|string|max:8|unique:generate_kode_grosir,kode_grosir,' . $data->id,
        ]);

        // cek duplicate di users
        if (!empty($validated['kode_grosir'])) {
            $existsUser = User::where(
                'kode_grosir',
                $validated['kode_grosir']
            )->exists();

            if ($existsUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode grosir sudah digunakan oleh user'
                ], 422);
            }
        }

        $data->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kode grosir berhasil diupdate',
            'data' => new GenerateKodeGrosirResource(
                $data->fresh()
            )
        ]);
    }

    /**
     * Delete kode grosir
     */
    public function destroy(string $id): JsonResponse
    {
        $data = Generate_kode_grosir::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Kode grosir tidak ditemukan'
            ], 404);
        }

        // cek apakah dipakai user
        $usedInUsers = User::where(
            'kode_grosir',
            $data->kode_grosir
        )->exists();

        if ($usedInUsers) {
            return response()->json([
                'success' => false,
                'message' => 'Kode grosir sedang digunakan user dan tidak bisa dihapus'
            ], 422);
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kode grosir berhasil dihapus'
        ]);
    }
}
