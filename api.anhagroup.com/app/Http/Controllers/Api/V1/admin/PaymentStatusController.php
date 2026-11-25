<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Models\Payment_status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PaymentStatusController extends Controller
{
    public function __construct(){}
    public function index(){
        try {
            $Payment_status = Payment_status::orderBy('id','asc');

            if ($Payment_status->exists()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment status berhasil ditemukan',
                    'data' => $Payment_status->paginate(100),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment status gagal ditemukan',
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