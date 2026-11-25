<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use DateTime;


class PromoController extends Controller{

    public function index(Request $request){
        try {
            $limit = $request->query('limit');
            $keyword = $request->query('q');

            $promos = Promo::
            select(
                'promos.id as promo_id',
                'barangs.id as barang_id',
                'nama_barang',
                DB::raw('CAST(promos.harga AS UNSIGNED) as promo_harga'),
                DB::raw('CAST(barangs.harga AS UNSIGNED) as barang_harga'),
                'satuan',
                'tgl_active',
                'tgl_non_active',
                DB::RAW("CONCAT('".url('/public/public/')."/',foto) as foto")
            )
            ->join('barangs','barangs.id','=','promos.id_barang')
            ->where('viewbe',1);
            if (!empty($keyword)) {
                $promos = $promos->where('nama_barang', 'like', '%' . $keyword . '%');
            }
            $promos = $promos->orderBy('promos.tgl_non_active', 'desc');
            
            if ($promos->exists()) {
                return response()->json([
                    'success' => true,
                    'message' => 'promo berhasil ditemukan',
                    'data' => $promos->paginate($limit)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'promo gagal ditemukan',
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan menghubungkan ke server',
            ], 500);
        }
    }
    public function cekInpPromo($request, $idpromo=null){
        $getTglAktif = Promo::select('tgl_active')
        ->where('id_barang',$request->id_barang);
        if (!empty($idpromo)) {
            $getTglAktif = $getTglAktif->where('id','<>',$idpromo);    
        }
        $getTglAktif = $getTglAktif->orderBy('tgl_active','asc')->first();

        if (empty($getTglAktif->tgl_active)) {
            return false;
        }
        

        $getTglNonAktif = Promo::select('tgl_non_active')
        ->where('id_barang',$request->id_barang);
        if (!empty($idpromo)) {
            $getTglNonAktif = $getTglNonAktif->where('id','<>',$idpromo);    
        }
        $getTglNonAktif = $getTglNonAktif->orderBy('tgl_non_active','desc')->first();

        if (empty($getTglNonAktif->tgl_non_active)) {
            return false;
        }



        $hisTglAktif = new DateTime($getTglAktif->tgl_active);
        $hisTglNonAktif = new DateTime($getTglNonAktif->tgl_non_active);


        $InputTglAktif = new DateTime($request->tgl_active);
        $InputTglNonAktif = new DateTime($request->tgl_non_active);


        if ($hisTglAktif <= $InputTglAktif && $InputTglAktif <= $hisTglNonAktif) {
            return true;
        }
        else if ($hisTglAktif <= $InputTglNonAktif && $InputTglNonAktif <= $hisTglNonAktif) {
            // echo "Tanggal sekarang berada di antara tanggal awal dan tanggal akhir.";
            return true;
        } else {
            // echo "Tanggal sekarang tidak berada di antara tanggal awal dan tanggal akhir.";
            return false;
        }

    }
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'id_barang' => 'required|integer',
            'harga' => 'required|integer',
            'tgl_active' => 'required|date_format:Y-m-d|after_or_equal:' . date('Y-m-d'),
            'tgl_non_active' => 'required|date_format:Y-m-d|after:tgl_active',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }

        $cekInpPromo = $this->cekInpPromo($request);

        if ($cekInpPromo) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal inputan produk yang serupa sudah terdaftar pada list promo',
            ], 400);
        }

        $promo = Promo::create([
            'id_barang' => $request->id_barang,
            'harga' => $request->harga,
            'tgl_active' => $request->tgl_active,
            'tgl_non_active' => $request->tgl_non_active,
        ]);

        try {
            if ($promo) {
                return response()->json([
                    'success' => true,
                    'message' => 'promo berhasil ditambahkan',
                    'data' => $promo
                ], 201);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'promo gagal ditambahkan',
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
            $promo = Promo::
            select(
                'promos.id as promo_id',
                'barangs.id as barang_id',
                'nama_barang',
                'barcode',
                DB::raw('CAST(promos.harga AS UNSIGNED) as promo_harga'),
                DB::raw('CAST(barangs.harga AS UNSIGNED) as barang_harga'),
                'satuan',
                'tgl_active',
                'tgl_non_active',
                DB::RAW("CONCAT('".url('/public/public/')."/',foto) as foto")
            )
            ->join('barangs','barangs.id','=','promos.id_barang')
            ->where('viewbe',1)
            ->where('promos.id',$id)
            ->orderBy('promos.tgl_non_active', 'desc')->first();
            if ($promo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Promo toko berhasil ditemukan',
                    'data' => $promo
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo toko gagal ditemukan',
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
        $promo = Promo::find($id);
        if (!$promo){
            return response()->json([
                'success' => false,
                'message' => 'Detail promo gagal ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_barang' => 'required|integer',
            'harga' => 'required|integer',
            'tgl_active' => 'required|date_format:Y-m-d',
            'tgl_non_active' => 'required|date_format:Y-m-d|after:tgl_active',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format data tidak sesuai '.$validator->errors()
            ], 400);
        }

        $cekTglInDb = Promo::where('id',$id)->first();
        // if ($cekTglInDb->tgl_active != $request->tgl_active && $cekTglInDb->tgl_non_active != $request->tgl_non_active) {
            $cekInpPromo = $this->cekInpPromo($request, $id);
            if ($cekInpPromo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal inputan produk yang serupa sudah terdaftar pada list promo',
                ], 400);
            }
        // }

        $update = $promo->update([
            'id_barang' => $request->id_barang,
            'harga' => $request->harga,
            'tgl_active' => $request->tgl_active,
            'tgl_non_active' => $request->tgl_non_active,
        ]);

        try {
            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'Promo toko berhasil diubah',
                    'data' => Promo::find($id),
                ]);
            }
            else
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo toko gagal diubah',
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
        $promo = Promo::find($id);
        if (!$promo){
            return response()->json([
                'success' => false,
                'message' => 'Detail promo gagal ditemukan',
            ], 404);
        }

        try {
            $delete = $promo->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'Promo toko berhasil dihapus',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo toko gagal dihapus',
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
