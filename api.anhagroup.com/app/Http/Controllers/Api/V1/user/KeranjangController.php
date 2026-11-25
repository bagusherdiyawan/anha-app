<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Keranjang;
use App\Models\SystemConfig;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KeranjangController extends Controller
{
    use ApiResponse;

    // Sinkronisasi harga terbaru sebelum load keranjang
    private function syncCartPrices(Request $request)
    {
        $keranjangs = Keranjang::with(['barang.hargaRitel', 'barang.hargaGrosir'])
            ->where('user_id', $request->user()->id)
            ->get();

        $role = $request->user()->role;

        foreach ($keranjangs as $item) {
            $latestPrice = $item->barang?->activePriceForRole($role);

            if ($latestPrice !== null && $item->harga != $latestPrice) {
                $item->harga = $latestPrice;
                $item->subtotal = $latestPrice * $item->qty;
                $item->save();
            }
        }
    }

    // Cek apakah harga aktif tersedia untuk item keranjang sesuai role user
    private function isPriceActive(Request $request, $keranjangItem): bool
    {
        return $keranjangItem->barang?->activePriceForRole($request->user()->role) !== null;
    }

    // tampilkan semua keranjang user
    public function index(Request $request)
    {
        $this->syncCartPrices($request);

        $keranjang = Keranjang::select('id', 'barang_id', 'qty', 'harga', 'subtotal')
            ->with([
                'barang:id,nama_barang,kode_barang,satuan,deskripsi,rating,stock,is_available',
                'barang.satuanBarang' => fn ($q) => $q->withTrashed()->select('id', 'nama'),
                'barang.hargaRitel',
                'barang.hargaGrosir',
            ])
            ->where('user_id', $request->user()->id)
            ->get();

        $items = $keranjang->transform(function ($item) use ($request) {
            $satuanNama = $item->barang?->resolvedSatuanName() ?? 'pcs';

            return [
                'id' => $item->id,
                'product_id' => $item->barang_id,
                'product_name' => $item->barang?->nama_barang,
                'product_code' => $item->barang?->kode_barang,
                'satuan' => $satuanNama,
                'satuan_nama' => $satuanNama,
                'image_url' => null,
                'unit_price' => (int) $item->harga,
                'quantity' => (int) $item->qty,
                'subtotal' => (int) $item->subtotal,
                'stock' => (int) $item->barang?->stock,
                'is_available' => $this->isPriceActive($request, $item)
                    && (bool) $item->barang?->is_available
                    && $item->barang?->stock > 0,
            ];
        });

        $subtotal_all = $keranjang->sum('subtotal');
        $total_point = $keranjang->count() > 0 ? intval($subtotal_all / SystemConfig::points_rate_per_point()) : 0;
        $minimumPurchase = (int) SystemConfig::get('minimum_purchase_amount', 0);
        $shippingEnabled = filter_var(SystemConfig::get('shipping_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $shippingFlatRate = max(0, (int) SystemConfig::get('shipping_flat_rate', 0));
        $shippingCost = $shippingEnabled ? $shippingFlatRate : 0;

        return $this->successResponse([
            'items' => $items,
            'items_count' => $keranjang->count(),
            'subtotal' => $subtotal_all,
            'estimated_points' => $total_point,
            'minimum_purchase_amount' => $minimumPurchase,
            'shipping_enabled' => $shippingEnabled,
            'shipping_flat_rate' => $shippingFlatRate,
            'shipping_cost' => $shippingCost,
        ]);
    }

    // tambah ke keranjang
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barang_id' => 'required|exists:barangs,id',
            'qty' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $barang = Barang::with(['hargaRitel', 'hargaGrosir'])->find($request->barang_id);
        $harga = $barang?->activePriceForRole($request->user()->role);

        if ($harga === null) {
            return $this->errorResponse('Barang ini sedang tidak aktif atau habis.', 400);
        }

        if (!$barang->is_available) {
            return $this->errorResponse('Barang ini sedang dinonaktifkan oleh toko.', 400);
        }

        // cek apakah barang sudah ada di keranjang
        $keranjang = Keranjang::where('user_id', $request->user()->id)
            ->where('barang_id', $request->barang_id)
            ->first();

        $requestedQty = $request->qty + ($keranjang->qty ?? 0);
        if ($requestedQty > $barang->stock) {
            return $this->errorResponse(
                'Stok tidak mencukupi. Sisa stok: ' . $barang->stock,
                400,
                ['stock' => $barang->stock]
            );
        }

        if ($keranjang) {
            $keranjang->qty += $request->qty;
            $keranjang->harga = $harga;
            $keranjang->subtotal = $keranjang->qty * $harga;
            $keranjang->save();
        } else {
            $keranjang = Keranjang::create([
                'user_id' => $request->user()->id,
                'barang_id' => $request->barang_id,
                'qty' => $request->qty,
                'harga' => $harga,
                'subtotal' => $harga * $request->qty,
            ]);
        }

        return $this->successResponse(
            [],
            'Barang berhasil ditambahkan ke keranjang'
        );
    }

    // update qty

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'qty' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $keranjang = Keranjang::with(['barang.hargaRitel', 'barang.hargaGrosir'])
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$keranjang) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang tidak ditemukan'
            ], 404);
        }

        if (!$keranjang->barang?->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Barang ini sedang dinonaktifkan oleh toko.',
            ], 400);
        }

        if ($request->qty > ($keranjang->barang?->stock ?? 0)) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi. Sisa stok: ' . ($keranjang->barang?->stock ?? 0),
                'stock' => $keranjang->barang?->stock ?? 0,
            ], 400);
        }

        $activePrice = $keranjang->barang?->activePriceForRole($request->user()->role);
        if ($activePrice !== null) {
            $keranjang->harga = $activePrice;
        }
        $keranjang->qty = $request->qty;
        $keranjang->subtotal = $keranjang->harga * $request->qty;
        $keranjang->save();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diupdate',
            'data' => $keranjang
        ]);
    }

    // hapus item keranjang
    public function destroy(Request $request, $id)
    {
        $keranjang = Keranjang::where('user_id', $request->user()->id)
            ->find($id);

        if (!$keranjang) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang tidak ditemukan'
            ], 404);
        }

        $keranjang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item keranjang berhasil dihapus'
        ]);
    }

    // kosongkan keranjang
    public function destroyAll(Request $request)
    {
        $keranjang = Keranjang::where('user_id', $request->user()->id)->get();

        if ($keranjang->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong'
            ], 400);
        }

        Keranjang::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil dikosongkan'
        ]);
    }
}
