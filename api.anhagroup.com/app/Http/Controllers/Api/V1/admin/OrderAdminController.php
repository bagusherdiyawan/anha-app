<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderAdminController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request)
    {
        try {
            $limit = $request->get('limit', 15);
            $search = $request->get('search');
            $status = $request->get('status');

            $query = Order::with(['user', 'metode_pembayaran'])
                ->orderBy('created_at', 'desc');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('invoice', 'LIKE', "%{$search}%")
                      ->orWhereHas('user', function($uq) use ($search) {
                          $uq->where('name', 'LIKE', "%{$search}%")
                             ->orWhere('phone', 'LIKE', "%{$search}%");
                      });
                });
            }

            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            $orders = $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            Log::error('OrderAdminController::index gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pesanan'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $order = Order::with([
                'user',
                'orderItems.barang',
                'alamat_pengiriman',
                'metode_pembayaran',
                'voucher'
            ])->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pesanan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Exception $e) {
            Log::error('OrderAdminController::show gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pesanan'
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,paid,confirmed,processed,delivering,completed,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $order = Order::with('orderItems')->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pesanan tidak ditemukan'
                ], 404);
            }

            $originalStatus = $order->status;

            $order->status = $request->status;
            $order->save();

            // Kembalikan stok barang & voucher saat order dibatalkan (hanya sekali, jaga idempotensi)
            if ($request->status === 'cancelled' && $originalStatus !== 'cancelled') {
                $this->orderService->restoreStock($order);
                $this->orderService->restoreVoucher($order);
            }

            // Hook: Award loyalty points hanya saat order benar-benar selesai
            if ($request->status === 'completed') {
                $this->orderService->awardPoints($order);
            } elseif ($request->status === 'cancelled') {
                // Tarik kembali poin kalau order ini sudah pernah kasih poin (completed lalu dibatalkan)
                $this->orderService->clawbackPoints($order);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status pesanan berhasil diperbarui',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            Log::error('OrderAdminController::updateStatus gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status pesanan'
            ], 500);
        }
    }
}
