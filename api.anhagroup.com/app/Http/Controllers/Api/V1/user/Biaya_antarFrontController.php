<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Models\Biaya_antar;
use Illuminate\Http\Request;

class Biaya_antarFrontController extends Controller
{
    public function index(){
        $biaya_antars = Biaya_antar::select('id','biaya')->where('status_active',1)->get();
        return response()->json([
            'message' => 'Daftar biaya antar berhasil diambil',
            'data' => $biaya_antars
        ]);
    }

}