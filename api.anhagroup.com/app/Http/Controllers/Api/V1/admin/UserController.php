<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // search
        $search = $request->q ?? $request->search;
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // filter role
        if ($request->role) {
            $query->where('role', $request->role);
        }

        $perPage = $request->input('per_page', $request->input('limit', 10));
        if ($perPage === 'all') {
            $perPage = 1000;
        }

        $users = $query
            ->latest()
            ->paginate((int) $perPage)
            ->appends($request->query());

        return UserResource::collection($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|unique:users,phone',
            'address' => 'nullable|string',
            'role' => 'required|string|max:6',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $photoPath = null;

        // upload photo
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos_user', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $this->generateKodeUnik(),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'password' => Hash::make($this->generateKodeUnik()),
            'role' => $validated['role'],
            'photo_url' => $photoPath,
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dibuat',
            'data' => new UserResource($user)
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $user = User::with('alamats')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail user',
            'data' => new UserResource($user)
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }


        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|unique:users,phone,' . $user->id,
            'address' => 'nullable|string',
            'role' => 'sometimes|required|string|max:6',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);



        // upload photo
        if ($request->hasFile('photo')) {
            // delete old photo
            if ($user->photo_url && Storage::disk('public')->exists($user->photo_url)) {
                Storage::disk('public')->delete($user->photo_url);
            }

            $validated['photo_url'] = $request
                ->file('photo')
                ->store('photos_user', 'public');
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diupdate',
            'data' => new UserResource($user->fresh())
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        // delete photo
        if ($user->photo_url && Storage::disk('public')->exists($user->photo_url)) {
            Storage::disk('public')->delete($user->photo_url);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }

    function generateKodeUnik()
    {
        do {
            $kode = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('email', $kode)->exists());

        return $kode;
    }
}
