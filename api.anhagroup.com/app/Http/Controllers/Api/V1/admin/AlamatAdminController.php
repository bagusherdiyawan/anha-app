<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlamatPengiriman\AlamatPengirimanResource;
use App\Models\Alamat;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlamatAdminController extends Controller
{
    /**
     * List alamat pengiriman
     */
    public function index(Request $request)
    {
        $query = Alamat::with('user');

        // search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q
                    ->where('recipient_name', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%')
                    ->orWhere('city', 'like', '%' . $request->search . '%')
                    ->orWhere('province', 'like', '%' . $request->search . '%');
            });
        }

        // filter user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $alamat = $query
            ->latest()
            ->paginate($request->per_page ?? 10)
            ->appends($request->query());

        return AlamatPengirimanResource::collection($alamat);
    }

    /**
     * Store alamat pengiriman
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'label' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'district' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'notes' => 'nullable|string',
            'default' => 'nullable|boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // jika default true
        if (!empty($validated['default'])) {
            Alamat::where('user_id', $validated['user_id'])
                ->update([
                    'default' => false
                ]);
        }

        $alamat = Alamat::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Alamat pengiriman berhasil dibuat',
            'data' => new AlamatPengirimanResource(
                $alamat->load('user')
            )
        ], 201);
    }

    /**
     * Detail alamat pengiriman
     */
    public function show(string $id): JsonResponse
    {
        $alamat = Alamat::with('user')->find($id);

        if (!$alamat) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat pengiriman tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail alamat pengiriman',
            'data' => new AlamatPengirimanResource($alamat)
        ]);
    }

    /**
     * Update alamat pengiriman
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $alamat = Alamat::find($id);

        if (!$alamat) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat pengiriman tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'label' => 'sometimes|required|string|max:255',
            'recipient_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'address' => 'sometimes|required|string',
            'district' => 'nullable|string|max:255',
            'city' => 'sometimes|required|string|max:255',
            'province' => 'sometimes|required|string|max:255',
            'postal_code' => 'sometimes|required|string|max:10',
            'notes' => 'nullable|string',
            'default' => 'nullable|boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // handle default address
        if (
            isset($validated['default']) &&
            $validated['default'] == true
        ) {
            Alamat::where(
                'user_id',
                $validated['user_id'] ?? $alamat->user_id
            )->update([
                'default' => false
            ]);
        }

        $alamat->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Alamat pengiriman berhasil diupdate',
            'data' => new AlamatPengirimanResource(
                $alamat->fresh()->load('user')
            )
        ]);
    }

    /**
     * Delete alamat pengiriman
     */
    public function destroy(string $id): JsonResponse
    {
        $alamat = Alamat::find($id);

        if (!$alamat) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat pengiriman tidak ditemukan'
            ], 404);
        }

        $alamat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alamat pengiriman berhasil dihapus'
        ]);
    }
}
