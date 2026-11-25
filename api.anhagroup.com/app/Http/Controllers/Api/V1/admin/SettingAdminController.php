<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SystemConfig\SystemConfigResource;
use App\Http\Resources\User\UserResource;
use App\Models\SystemConfig;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingAdminController extends Controller
{
    /**
     * List settings
     */
    public function index()
    {
        $configs = SystemConfig::query()
            ->orderBy('group')
            ->orderBy('key')
            ->paginate(20);

        return SystemConfigResource::collection($configs);
    }

    /**
     * Update settings
     */
    public function update(Request $request): JsonResponse
    {
        $settings = [
            'whatsapp_number',
            'phone_number',
            'email_contact',
            'whatsapp_template',
            'shipping_enabled',
            'shipping_flat_rate',
            'points_rate_per_point',
            'points_value_in_rupiah',
            'points_expiry_days',
            'order_cancel_minutes',
            'low_stock_threshold',
        ];

        foreach ($settings as $key) {
            if ($request->has($key)) {
                SystemConfig::set(
                    $key,
                    $request->input($key)
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan'
        ]);
    }

    /**
     * List admin
     */
    public function admins(Request $request)
    {
        $query = User::query()
            ->where('role', 'admin')
            ->latest();

        // search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where(
                    'name',
                    'like',
                    '%' . $request->search . '%'
                )->orWhere(
                    'email',
                    'like',
                    '%' . $request->search . '%'
                );
            });
        }

        $admins = $query
            ->paginate($request->per_page ?? 10)
            ->appends($request->query());

        return UserResource::collection($admins);
    }

    /**
     * Create admin
     */
    public function storeAdmin(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'name' =>
                'required|string|max:255',
            'email' =>
                'required|email|unique:users,email',
            'password' =>
                'required|string|min:8',
        ]);

        $admin = User::create([
            'role' => 'admin',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(
                $validated['password']
            ),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin berhasil ditambahkan',
            'data' => new UserResource($admin)
        ], 201);
    }

    /**
     * Toggle admin active
     */
    public function toggleAdmin(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Admin tidak ditemukan'
            ], 404);
        }

        // prevent self toggle
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menonaktifkan akun sendiri'
            ], 422);
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status admin berhasil diperbarui',
            'data' => new UserResource($user->fresh())
        ]);
    }

    /**
     * Delete admin
     */
    public function deleteAdmin(
        string $id
    ): JsonResponse {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Admin tidak ditemukan'
            ], 404);
        }

        // prevent self delete
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus akun sendiri'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin berhasil dihapus'
        ]);
    }
}
