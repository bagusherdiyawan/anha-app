<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    public function ctrl_show_profil(Request $request)
    {
        $id = $request->id;
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'User profil fetched successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'role' => $user->role,
                    'kode_grosir' => $user->kode_grosir,
                    'email_verified_at' => $user->email_verified_at,
                    'photo_url' => $user->photo_url,
                ],
            ]
        ], 200);
    }

    public function ctrl_update_profil(Request $request){
        $id = $request->id;
        $user = User::find($id);
        if (!$user){
            return response()->json([
                'success' => false,
                'message' => 'Detail user gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'address' => 'required|string',
            'photo_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }

        $foto_root = substr($user->photo_url, strpos($user->photo_url, 'photo_url'));
        $fotoPath = $foto_root;

        if ($request->hasFile('photo_url')) {
            if ($foto_root && Storage::disk('public')->exists($foto_root)) {
                Storage::disk('public')->delete($foto_root);
            }
            $fotoPath = $request->file('photo_url')->store('photo_url', 'public');
        } elseif ($request->input('clear_foto') == 'ya') {
            if ($foto_root && Storage::disk('public')->exists($foto_root)) {
                Storage::disk('public')->delete($foto_root);
            }
            $fotoPath = null;
        }

        $userup = $user->update([
            'name' => $request->name,
            'phone' => $request->phone ?? $user->phone,
            'address' => $request->address ?? $user->address,
            'photo_url' => $fotoPath,
        ]);


        try {
            if ($userup) {
                return response()->json([
                    'success' => true,
                    'message' => 'User berhasil diubah',
                    'data' => User::find($request->id),
                ]);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'User gagal diubah',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }
}