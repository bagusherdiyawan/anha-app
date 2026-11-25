<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Models\Biaya_antar_batas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Biaya_antar_batasFrontController extends Controller
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
}