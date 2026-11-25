<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Models\Biaya_antar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Biaya_antarController extends Controller
{
    public function index(){
        $biaya_antars = Biaya_antar::orderBy('id', 'desc')->get();
        return response()->json([
            'message' => 'Daftar biaya antar berhasil diambil',
            'data' => $biaya_antars
        ]);
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'biaya' => 'required|numeric',
            'status_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }

        $biaya_antar = Biaya_antar::create($validator->validated());

        try {
            if ($biaya_antar) {
                return response()->json([
                    'success' => true,
                    'message' => 'biaya antar berhasil ditambahkan',
                    'data' => $biaya_antar
                ], 201);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'biaya antar gagal ditambahkan',
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }
    public function show($id){
        try {
            $biaya_antar = Biaya_antar::where('id',$id)->get();
            if ($biaya_antar) {
                return response()->json([
                    'success' => true,
                    'message' => 'Biaya antar berhasil ditemukan',
                    'data' => $biaya_antar
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Biaya antar gagal ditemukan',
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
        $biaya_antar = Biaya_antar::find($id);
        if (!$biaya_antar){
            return response()->json([
                'success' => false,
                'message' => 'Detail biaya antar gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'biaya' => 'required|numeric',
            'status_active' => 'nullable|boolean',
        ]);

         if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }

        $update = $biaya_antar->update($validator->validated());

        try {
            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'Biaya antar berhasil diubah',
                    'data' => Biaya_antar::find($id),
                ]);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Biaya antar gagal diubah',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }
    public function destroy($id){
        $biaya_antar = Biaya_antar::find($id);
        if (!$biaya_antar){
            return response()->json([
                'success' => false,
                'message' => 'Detail biaya antar gagal ditemukan',
            ], 404);
        }

        try {
            $delete = $biaya_antar->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'Biaya antar berhasil dihapus',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Biaya antar gagal dihapus',
                ], 500);
            } 
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungkan ke server',
            ], 500);
        }
    }
}
