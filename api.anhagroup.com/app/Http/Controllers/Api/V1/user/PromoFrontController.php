<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PromoFrontController extends Controller
{
    public function index(){
        $tglNow = date('Y-m-d');

        try {
            $promos = Promo::select(
                'barangs.id as id',
                'promos.id as promo_id',
                'nama_barang',
                DB::raw('CAST(barangs.harga AS UNSIGNED) as harga'),
                DB::raw('CAST(promos.harga AS UNSIGNED) as promo_harga'),
                'barangs.satuan',
                DB::raw("COALESCE(NULLIF(barangs_satuan.nama, ''), barangs.satuan) as satuan_nama"),
                'tgl_active',
                'tgl_non_active',
                DB::RAW("CONCAT('".url('/public/public/')."/',foto) as foto")
            )
            ->join('barangs','barangs.id','=','promos.id_barang')
            ->leftJoin('barangs_satuan', 'barangs_satuan.id', '=', 'barangs.satuan')
            ->whereDate('tgl_active', '<=', $tglNow)
            ->whereDate('tgl_non_active', '>=', $tglNow)
            ->orderBy('tgl_non_active', 'desc');

            if ($promos->exists()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Promo berhasil ditemukan',
                    'data' => $promos->get()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo gagal ditemukan',
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