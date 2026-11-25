<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    use ApiResponse;

    function index(Request $request)
    {
        $id = $request->user()->id;
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('Detail user gagal ditemukan', 404);
        }

        $totalOrders = \App\Models\Order::where('user_id', $user->id)->count();

        return $this->successResponse([
            'id' => $user->id,
            'type' => $user->role,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'subscription_number' => $user->subscription_number,
            'company_name' => $user->company_name,
            'photo_url' => $user->photo_url,
            'points' => (int) $user->points,
            'total_orders' => $totalOrders,
            'is_active' => true,
            'created_at' => $user->created_at->format('Y-m-d H:i:s')
        ]);
    }

    public function ctrl_update(Request $request, $id = null)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'address' => 'nullable|string',
            'password' => 'nullable|string|min:6',
            'photo_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Format data tidak sesuai: ' . $validator->errors()->first(), 400);
        }

        $data = $request->only([
            'name',
            'phone',
            'address',
        ]);

        // update password hanya jika dikirim
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // handle foto
        $foto_root = $user->photo_url
            ? substr($user->photo_url, strpos($user->photo_url, 'photos_url'))
            : null;

        $file = $request->file('photo_url') ?? $request->file('photo');
        if ($file) {
            if ($foto_root && Storage::disk('public')->exists($foto_root)) {
                Storage::disk('public')->delete($foto_root);
            }

            $data['photo_url'] = $file->store('photos_url', 'public');
        } elseif ($request->input('clear_foto') == 'ya') {
            if ($foto_root && Storage::disk('public')->exists($foto_root)) {
                Storage::disk('public')->delete($foto_root);
            }

            $data['photo_url'] = null;
        }

        try {
            $user->update($data);
            $user->refresh();

            $totalOrders = \App\Models\Order::where('user_id', $user->id)->count();

            return $this->successResponse([
                'id' => $user->id,
                'type' => $user->role,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'subscription_number' => $user->subscription_number,
                'company_name' => $user->company_name,
                'photo_url' => $user->photo_url,
                'points' => (int) $user->points,
                'total_orders' => $totalOrders,
                'is_active' => true,
                'created_at' => $user->created_at->format('Y-m-d H:i:s')
            ], 'Profil berhasil diperbarui');
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat menghubungkan ke server: ' . $e->getMessage(),
                500
            );
        }
    }

    public function ctrl_update_foto(Request $request, $id = null)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'photo_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Format data tidak sesuai: ' . $validator->errors()->first(), 400);
        }

        $data = [];

        // handle foto
        $foto_root = $user->photo_url
            ? substr($user->photo_url, strpos($user->photo_url, 'photos_url'))
            : null;

        $file = $request->file('photo_url') ?? $request->file('photo');
        if ($file) {
            if ($foto_root && Storage::disk('public')->exists($foto_root)) {
                Storage::disk('public')->delete($foto_root);
            }

            $data['photo_url'] = $file->store('photos_url', 'public');
        } elseif ($request->input('clear_foto') == 'ya') {
            if ($foto_root && Storage::disk('public')->exists($foto_root)) {
                Storage::disk('public')->delete($foto_root);
            }

            $data['photo_url'] = null;
        }

        if (empty($data)) {
            return $this->errorResponse('Tidak ada file foto yang dikirim', 400);
        }

        try {
            $user->update($data);
            $user->refresh();

            return $this->successResponse([
                'photo_url' => $user->photo_url,
                'full_photo_url' => $user->photo_url ? (str_starts_with($user->photo_url, 'http') ? $user->photo_url : asset('storage/' . $user->photo_url)) : null,
            ], 'Foto profil berhasil diperbarui');
        } catch (Exception $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat menghubungkan ke server: ' . $e->getMessage(),
                500
            );
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Format data tidak sesuai ' . $validator->errors(), 400);
        }

        try {
            $passwordup = $request->user()->update(['password' => Hash::make($request->password)]);
            if ($passwordup) {
                return response()->json([
                    'message' => 'Password berhasil diubah',
                ], 200);
            } else {
                return $this->errorResponse('Password gagal diubah', 404);
            }
        } catch (Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghubungkan ke server', 500);
        }
    }
}
