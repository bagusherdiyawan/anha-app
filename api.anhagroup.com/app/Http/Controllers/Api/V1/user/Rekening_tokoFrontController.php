<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Models\Rekening_toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class Rekening_tokoFrontController extends Controller
{
    public function index()
    {
        try {
            $rekenings = Rekening_toko::where('status_active', 1)->orderBy('type', 'asc')->orderBy('nama', 'asc')->get();
            return response()->json([
                'success' => true,
                'message' => 'rekening berhasil ditemukan',
                'data' => $rekenings
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }

    public function withCaraBayar()
    {
        try {
            $rekenings = Rekening_toko::where('status_active', 1)->orderBy('type', 'asc')->orderBy('nama', 'asc')->get();
            return response()->json([
                'success' => true,
                'message' => 'rekening berhasil ditemukan',
                'data' => $rekenings
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }
}
