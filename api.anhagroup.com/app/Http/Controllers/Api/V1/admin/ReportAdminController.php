<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportAdminController extends Controller
{
    /**
     * Laporan Penjualan
     */
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->get('end_date', Carbon::now()->toDateString());
        $groupBy   = $request->get('group_by', 'day'); // day | month

        $dateFormat = $groupBy === 'month' ? '%Y-%m' : '%Y-%m-%d';

        // Summary stats
        $summary = DB::table('orders')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_revenue')
            ->first();

        $cancelledCount = DB::table('orders')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'cancelled')
            ->count();

        // Chart data (grouped by day/month)
        $chartData = DB::table('orders')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', '!=', 'cancelled')
            ->selectRaw("DATE_FORMAT(created_at, '$dateFormat') as period, COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Recent orders table
        $orders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'orders.id',
                'orders.invoice',
                'orders.total',
                'orders.status',
                'orders.created_at',
                'users.name as customer_name',
                'users.email as customer_email'
            )
            ->orderBy('orders.created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_orders'   => $summary->total_orders ?? 0,
                    'total_revenue'  => $summary->total_revenue ?? 0,
                    'avg_order'      => $summary->total_orders > 0
                        ? round($summary->total_revenue / $summary->total_orders)
                        : 0,
                    'cancelled'      => $cancelledCount,
                ],
                'chart'   => $chartData,
                'orders'  => $orders,
            ]
        ]);
    }

    /**
     * Laporan Stok
     */
    public function stock(Request $request)
    {
        $search    = $request->get('search');
        $threshold = (int) $request->get('threshold', 10);
        $category  = $request->get('category');

        // Summary
        $total        = DB::table('barangs')->count();
        $outOfStock   = DB::table('barangs')->where('stock', 0)->count();
        $lowStock     = DB::table('barangs')->where('stock', '>', 0)->where('stock', '<=', $threshold)->count();
        $totalStock   = DB::table('barangs')->sum('stock');

        // Product list
        $query = DB::table('barangs')
            ->leftJoin('kategori_barangs', 'barangs.kode_kategori', '=', 'kategori_barangs.kode')
            ->select(
                'barangs.id',
                'barangs.kode_barang',
                'barangs.nama_barang',
                'barangs.stock',
                'barangs.satuan',
                'kategori_barangs.nama as nama_kategori'
            )
            ->orderBy('barangs.stock', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('barangs.nama_barang', 'LIKE', "%$search%")
                  ->orWhere('barangs.kode_barang', 'LIKE', "%$search%");
            });
        }

        if ($category) {
            $query->where('barangs.kode_kategori', $category);
        }

        $products = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_products' => $total,
                    'out_of_stock'   => $outOfStock,
                    'low_stock'      => $lowStock,
                    'total_stock'    => $totalStock,
                ],
                'products' => $products,
            ]
        ]);
    }

    /**
     * Laporan Pelanggan
     */
    public function customers(Request $request)
    {
        $search    = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        // Summary
        $total = DB::table('users')->where('role', '!=', 'admin')->orWhereNull('role')->count();
        $thisMonth = DB::table('users')
            ->where(function ($q) {
                $q->where('role', '!=', 'admin')->orWhereNull('role');
            })
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $activeCustomers = DB::table('users')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where(function ($q) {
                $q->where('users.role', '!=', 'admin')->orWhereNull('users.role');
            })
            ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
            ->distinct('users.id')
            ->count('users.id');

        // Customer table
        $query = DB::table('users')
            ->leftJoin('orders', function ($join) {
                $join->on('users.id', '=', 'orders.user_id')
                    ->where('orders.status', '!=', 'cancelled');
            })
            ->where(function ($q) {
                $q->where('users.role', '!=', 'admin')->orWhereNull('users.role');
            })
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                'users.created_at',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('COALESCE(SUM(orders.total), 0) as total_spent')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.phone', 'users.created_at')
            ->orderBy('total_spent', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'LIKE', "%$search%")
                  ->orWhere('users.email', 'LIKE', "%$search%")
                  ->orWhere('users.phone', 'LIKE', "%$search%");
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('users.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $customers = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total'         => $total,
                    'this_month'    => $thisMonth,
                    'active_30d'    => $activeCustomers,
                ],
                'customers' => $customers,
            ]
        ]);
    }

    /**
     * Laporan Poin
     */
    public function points(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->get('end_date', Carbon::now()->toDateString());
        $type      = $request->get('type');

        // Summary
        $totalEarned = DB::table('point_transactions')
            ->where('type', 'earn')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');

        $totalRedeemed = DB::table('point_transactions')
            ->where('type', 'redeem')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');

        $totalAdjusted = DB::table('point_transactions')
            ->where('type', 'adjustment')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('amount');

        // Transactions list
        $query = DB::table('point_transactions')
            ->join('users', 'point_transactions.user_id', '=', 'users.id')
            ->whereBetween('point_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'point_transactions.id',
                'point_transactions.type',
                'point_transactions.amount',
                'point_transactions.balance_after',
                'point_transactions.description',
                'point_transactions.created_at',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('point_transactions.created_at', 'desc');

        if ($type && $type !== 'all') {
            $query->where('point_transactions.type', $type);
        }

        $transactions = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_earned'   => (int) $totalEarned,
                    'total_redeemed' => (int) abs($totalRedeemed),
                    'total_adjusted' => (int) $totalAdjusted,
                    'net_issued'     => (int) ($totalEarned + $totalRedeemed + $totalAdjusted),
                ],
                'transactions' => $transactions,
            ]
        ]);
    }
}
