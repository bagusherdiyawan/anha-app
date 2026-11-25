<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // KPI Calculations
            $total_revenue = Order::whereIn('status', ['paid', 'completed'])->sum('total');
            $total_orders = Order::count();
            $total_users = User::whereIn('role', ['grosir', 'ritel'])->count();
            $total_products = Barang::count();

            // Recent Orders
            $recent_orders = Order::select(
                'orders.id',
                'us.name as nama_user',
                'orders.created_at as tanggal',
                'orders.total',
                'orders.status',
                'rt.nama as nama_rekening'
            )
            ->leftJoin('users as us','us.id','=','orders.user_id')
            ->leftJoin('rekening_tokos as rt','rt.id','=','orders.metode_pembayaran_id')
            ->orderBy('orders.id','desc')
            ->take(6)
            ->get();

            // Revenue Chart Data (Last 7 Days)
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $revenue = Order::whereDate('created_at', $date->format('Y-m-d'))
                                  ->whereIn('status', ['paid', 'completed'])
                                  ->sum('total');
                
                $chartData[] = [
                    'name' => $date->translatedFormat('D'), // E.g., 'Sen', 'Sel' (if locale supports, else 'Mon', 'Tue')
                    'revenue' => (int)$revenue
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'kpis' => [
                        'total_revenue' => $total_revenue,
                        'total_orders' => $total_orders,
                        'total_users' => $total_users,
                        'total_products' => $total_products
                    ],
                    'chart_data' => $chartData,
                    'recent_orders' => $recent_orders
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('DashboardController::index gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data analitik'
            ], 500);
        }
    }
}
