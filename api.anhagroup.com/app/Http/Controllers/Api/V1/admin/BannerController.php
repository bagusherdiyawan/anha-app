<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Banner\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Banner::query();

        // search
        $search = $request->q ?? $request->search;
        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // filter active
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $perPage = $request->input('per_page', $request->input('limit', 10));
        if ($perPage === 'all') {
            $perPage = 1000;
        }

        $banners = $query
            ->orderBy('sort_order', 'asc')
            ->latest()
            ->paginate((int) $perPage);

        return BannerResource::collection($banners);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description' => 'nullable|string',
            'deep_link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        // upload image
        $imagePath = $request->file('image')->store('photos_banner', 'public');

        $banner = Banner::create([
            'title' => $validated['title'],
            'image_path' => $imagePath,
            'description' => $validated['description'] ?? null,
            'deep_link' => $validated['deep_link'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner berhasil dibuat',
            'data' => new BannerResource($banner)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Banner',
            'data' => new BannerResource($banner)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description' => 'nullable|string',
            'deep_link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        // update image
        if ($request->hasFile('image')) {
            // hapus image lama
            if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }

            $imagePath = $request->file('image')->store('photos_banner', 'public');

            $banner->image_path = $imagePath;
        }

        $banner->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'deep_link' => $validated['deep_link'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner berhasil diupdate',
            'data' => new BannerResource($banner)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner tidak ditemukan'
            ], 404);
        }

        // hapus image
        if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
            Storage::disk('public')->delete($banner->image_path);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner berhasil dihapus'
        ]);
    }
}
