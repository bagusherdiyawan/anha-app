<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BannerFrontController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $banners = Banner::where('is_active', true)->orderBy('sort_order', 'asc')->get(['id','title','description','image_path']);
            return $this->successResponse(
                $banners,
                'Banner berhasil ditemukan'
            );
        } catch (\Throwable $e) {
            return $this->errorResponse('Terjadi kesalahan menghubungkan ke server', 500);
        }
    }
}
