<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RewardAdminController extends Controller
{
    public function index(Request $request)
    {
        try {
            $limit = $request->input('limit', $request->input('per_page', 15));
            if ($limit === 'all') {
                $limit = 1000;
            }
            $search = $request->q ?? $request->get('search');
            $type = $request->get('type');
            $status = $request->get('status');

            $query = Reward::with('barang')->orderBy('id', 'desc');

            if ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            if ($type && $type !== 'all') {
                $query->where('type', $type);
            }

            if ($status !== null && $status !== 'all') {
                $query->where('is_active', $status);
            }

            $rewards = $query->paginate((int) $limit);

            $rewards->getCollection()->transform(function ($reward) {
                $reward->name = $reward->resolvedName();
                $reward->description = $reward->resolvedDescription();
                $reward->image_url = $reward->resolvedImageUrl();
                return $reward;
            });

            return response()->json([
                'success' => true,
                'data' => $rewards
            ]);
        } catch (\Exception $e) {
            Log::error('RewardAdminController::index gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data program reward'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:voucher_discount,direct_discount,physical_item',
                'barang_id' => 'required_if:type,physical_item|nullable|exists:barangs,id',
                'name' => 'required_if:type,voucher_discount,direct_discount|nullable|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'points_required' => 'required|integer|min:1',
                'stock' => 'required|integer|min:-1',
                'discount_value' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:fixed,percentage',
                'max_discount' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except('image');

            if ($data['type'] === 'physical_item') {
                $data['name'] = null;
                $data['description'] = null;
            } else {
                $data['barang_id'] = null;
            }

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('photos_reward', 'public');
            }

            $reward = Reward::create($data);
            $reward->load('barang');

            $reward->name = $reward->resolvedName();
            $reward->description = $reward->resolvedDescription();
            $reward->image_url = $reward->resolvedImageUrl();

            return response()->json([
                'success' => true,
                'message' => 'Reward berhasil ditambahkan',
                'data' => $reward
            ], 201);
        } catch (\Exception $e) {
            Log::error('RewardAdminController::store gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan reward'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $reward = Reward::with('barang')->find($id);

            if (!$reward) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data reward tidak ditemukan'
                ], 404);
            }

            $reward->name = $reward->resolvedName();
            $reward->description = $reward->resolvedDescription();
            $reward->image_url = $reward->resolvedImageUrl();

            return response()->json([
                'success' => true,
                'data' => $reward
            ]);
        } catch (\Exception $e) {
            Log::error('RewardAdminController::show gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail reward'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $reward = Reward::find($id);

            if (!$reward) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data reward tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'required|in:voucher_discount,direct_discount,physical_item',
                'barang_id' => 'required_if:type,physical_item|nullable|exists:barangs,id',
                'name' => 'required_if:type,voucher_discount,direct_discount|nullable|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'points_required' => 'required|integer|min:1',
                'stock' => 'required|integer|min:-1',
                'discount_value' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:fixed,percentage',
                'max_discount' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except('image', '_method');

            if ($request->hasFile('image')) {
                // Delete old image
                if ($reward->image && Storage::disk('public')->exists($reward->image)) {
                    Storage::disk('public')->delete($reward->image);
                }
                $data['image'] = $request->file('image')->store('photos_reward', 'public');
            }

            // Provide default null for discount related fields if not provided or type changed
            if ($data['type'] === 'physical_item') {
                $data['discount_value'] = null;
                $data['discount_type'] = null;
                $data['max_discount'] = null;
                $data['name'] = null;
                $data['description'] = null;
            } else {
                $data['barang_id'] = null;
            }

            $reward->update($data);
            $reward->load('barang');

            $reward->name = $reward->resolvedName();
            $reward->description = $reward->resolvedDescription();
            $reward->image_url = $reward->resolvedImageUrl();

            return response()->json([
                'success' => true,
                'message' => 'Reward berhasil diperbarui',
                'data' => $reward
            ]);
        } catch (\Exception $e) {
            Log::error('RewardAdminController::update gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui reward'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $reward = Reward::find($id);

            if (!$reward) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data reward tidak ditemukan'
                ], 404);
            }

            $reward->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reward berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('RewardAdminController::destroy gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus reward'
            ], 500);
        }
    }

    public function toggleActive($id)
    {
        try {
            $reward = Reward::find($id);

            if (!$reward) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data reward tidak ditemukan'
                ], 404);
            }

            $reward->is_active = !$reward->is_active;
            $reward->save();

            return response()->json([
                'success' => true,
                'message' => 'Status aktif reward berhasil diubah',
                'data' => $reward
            ]);
        } catch (\Exception $e) {
            Log::error('RewardAdminController::toggleActive gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status reward'
            ], 500);
        }
    }
}
