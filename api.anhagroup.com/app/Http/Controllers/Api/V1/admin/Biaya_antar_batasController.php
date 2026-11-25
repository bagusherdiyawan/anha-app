<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Models\Biaya_antar_batas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Biaya_antar_batasController extends Controller
{
    public function index(){
        try {
            $biaya_antar_batas = Biaya_antar_batas::select('id','nominal')->find(1);
            if ($biaya_antar_batas) {
                return response()->json([
                    'success' => true,
                    'message' => 'Detail batas biaya antar berhasil ditemukan',
                    'data' => $biaya_antar_batas
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail batas biaya antar gagal ditemukan',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }
    public function update(Request $request, $id){
        $biaya_antar_batas = Biaya_antar_batas::find($id);
        if (!$biaya_antar_batas){
            return response()->json([
                'success' => false,
                'message' => 'Detail batas biaya antar gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(),[
            'nominal' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }


        $biaya_antar_batas->update([
            'nominal' => $request->nominal,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Batas biaya antar berhasil diperbarui',
            'data' => $biaya_antar_batas
        ]);
    }
}