<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class TokoController extends Controller
{
    public function index(){
        try {
            $toko = Toko::find(1);
            if ($toko) {
                return response()->json([
                    'success' => true,
                    'message' => 'Detail toko berhasil ditemukan',
                    'data' => $toko
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail toko gagal ditemukan',
                ], 404);
            }
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nama_toko' => 'required|string|max:255|unique:tokos,nama_toko',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('photos_logo', 'public');
        }


        try {
            $toko = Toko::create([
                'logo' => $logoPath,
                'nama_toko' => $request->nama_toko,
                'alamat' => $request->alamat,
                'nomor_telepon' => $request->nomor_telepon,
            ]);

            if ($toko) {
                return response()->json([
                    'success' => true,
                    'message' => 'Toko berhasil ditambahkan',
                    'data' => $toko
                ], 201);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko gagal ditambahkan',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat mencoba logout',
            ], 500);
        }
    }
    public function show(Toko $id){
        try {
            $toko = Toko::find(1);
            if ($toko) {
                return response()->json([
                    'success' => true,
                    'message' => 'Detail toko berhasil ditemukan',
                    'data' => $toko
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail toko gagal ditemukan',
                ], 404);
            }
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }
    public function update(Request $request, $id){
        $toko = Toko::find($id);
        if (!$toko){
            return response()->json([
                'success' => false,
                'message' => 'Detail toko gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(),[
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nama_toko' => ['required','string','max:255',Rule::unique('tokos')->ignore($toko->id, 'id') ],
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }

        $logo_root = substr($toko->logo, strpos($toko->logo, 'photos_logo'));
        $logoPath = $logo_root;

        if ($request->hasFile('logo')) {
            if ($logo_root && Storage::disk('public')->exists($logo_root)) {
                Storage::disk('public')->delete($logo_root);
            }
            $logoPath = $request->file('logo')->store('photos_logo', 'public');
        } elseif ($request->input('clear_logo') == 'ya') {
            if ($logo_root && Storage::disk('public')->exists($logo_root)) {
                Storage::disk('public')->delete($logo_root);
            }
            $logoPath = null;
        }

        $toko->update([
            'logo' => $logoPath,
            'nama_toko' => $request->nama_toko,
            'alamat' => $request->alamat,
            'nomor_telepon' => $request->nomor_telepon,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Toko berhasil diperbarui',
            'data' => $toko
        ]);
    }
}