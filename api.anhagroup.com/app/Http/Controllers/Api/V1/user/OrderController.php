<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    /*
     * |--------------------------------------------------------------------------
     * | Riwayat Pesanan User
     * |--------------------------------------------------------------------------
     */

    private function orderRelations(): array
    {
        return [
            'alamat_pengiriman:id,label,recipient_name,phone,address,district,city,province,postal_code,notes',
            'orderItems:id,order_id,barang_id,qty,harga,subtotal',
            'orderItems.barang:id,nama_barang,kode_barang,satuan,deskripsi,rating,stock,is_available',
            'orderItems.barang.satuanBarang' => fn ($q) => $q->withTrashed()->select('id', 'nama'),
            'orderItems.barang.fotos:id,id_barang,url_foto_barang',
            'orderItems.barang.hargaRitel',
            'orderItems.barang.hargaGrosir',
        ];
    }

    private function enrichOrderItems($orders, ?string $role): void
    {
        $collection = $orders instanceof \Illuminate\Support\Collection ? $orders : collect([$orders]);
        foreach ($collection as $order) {
            foreach ($order?->orderItems ?? [] as $item) {
                $barang = $item->barang;
                if (!$barang) {
                    continue;
                }

                $satuanNama = $barang->resolvedSatuanName();
                $barang->setAttribute('satuan_nama', $satuanNama);
                $barang->setAttribute('satuan', $satuanNama);

                $fotoUrls = $barang->fotoUrls();
                if (!empty($fotoUrls)) {
                    $barang->setAttribute('primary_image_url', $fotoUrls[0]);
                }

                $activePrice = $barang->activePriceForRole($role);
                $roleFallbackPrice = $role === 'grosir'
                    ? ($barang->hargaGrosir?->harga ?? $barang->hargaRitel?->harga)
                    : ($barang->hargaRitel?->harga ?? $barang->hargaGrosir?->harga);
                $resolvedPrice = $activePrice ?? $roleFallbackPrice ?? $item->harga ?? 0;

                $barang->setAttribute('harga', (int) $resolvedPrice);
                $barang->setAttribute('price', (int) $resolvedPrice);
                $barang->setAttribute('is_available', $activePrice !== null && (bool) $barang->is_available);
                $barang->makeHidden(['hargaRitel', 'hargaGrosir']);
            }
        }
    }

    public function index(Request $request)
    {
        try {
            $orders = Order::with($this->orderRelations())
                ->where('user_id', $request->user()->id)
                ->latest()
                ->get();

            $this->enrichOrderItems($orders, $request->user()?->role);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat pesanan berhasil ditemukan',
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            Log::error('OrderController::index gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
            ], 500);
        }
    }

    /*
     * |--------------------------------------------------------------------------
     * | Detail Pesanan
     * |--------------------------------------------------------------------------
     */

    public function show($id, Request $request)
    {
        try {
            $order = Order::with($this->orderRelations())
                ->where('user_id', $request->user()->id)
                ->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak ditemukan',
                ], 404);
            }

            $this->enrichOrderItems($order, $request->user()?->role);

            return response()->json([
                'success' => true,
                'message' => 'Detail pesanan berhasil ditemukan',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            Log::error('OrderController::show gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
            ], 500);
        }
    }

    /*
     * |--------------------------------------------------------------------------
     * | Konfirmasi Terima Barang (customer)
     * |--------------------------------------------------------------------------
     */

    public function confirmReceived($id, Request $request)
    {
        try {
            $order = Order::where('user_id', $request->user()->id)->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak ditemukan',
                ], 404);
            }

            if ($order->status !== 'delivering') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan hanya dapat dikonfirmasi diterima saat status sedang diantar',
                ], 422);
            }

            $order->status = 'completed';
            $order->save();

            $this->orderService->awardPoints($order);

            return response()->json([
                'success' => true,
                'message' => 'Terima kasih, pesanan telah dikonfirmasi diterima',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            Log::error('OrderController::confirmReceived gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
            ], 500);
        }
    }

    /*
     * |--------------------------------------------------------------------------
     * | Batalkan Pesanan (customer)
     * |--------------------------------------------------------------------------
     */

    public function cancel($id, Request $request)
    {
        try {
            $order = Order::with('orderItems')->where('user_id', $request->user()->id)->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak ditemukan',
                ], 404);
            }

            if (!in_array($order->status, ['pending', 'paid'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan yang sudah diproses toko tidak dapat dibatalkan sendiri. Silakan hubungi bantuan toko.',
                ], 422);
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                $order->status = 'cancelled';
                $order->save();

                $this->orderService->restoreStock($order);
                $this->orderService->restoreVoucher($order);
                $this->orderService->clawbackPoints($order);
            });

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibatalkan',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            Log::error('OrderController::cancel gagal', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
            ], 500);
        }
    }
}
