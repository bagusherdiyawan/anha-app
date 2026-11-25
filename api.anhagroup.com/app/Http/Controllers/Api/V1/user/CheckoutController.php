<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
// use App\Mail\NotifCheckout;
use App\Models\Alamat;
use App\Models\Barang;
use App\Models\Keranjang;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SystemConfig;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Exception;

class CheckoutController extends Controller
{
    private function syncCartPrices(Request $request)
    {
        $keranjangs = Keranjang::with(['barang.hargaRitel', 'barang.hargaGrosir'])
            ->where('user_id', $request->user()->id)
            ->get();

        $role = $request->user()->role;

        foreach ($keranjangs as $item) {
            $latestPrice = null;
            if ($role == 'grosir' && $item->barang->hargaGrosir?->is_active) {
                $latestPrice = $item->barang->hargaGrosir->harga;
            } elseif ($role == 'ritel' && $item->barang->hargaRitel?->is_active) {
                $latestPrice = $item->barang->hargaRitel->harga;
            }

            if ($latestPrice !== null && $item->harga != $latestPrice) {
                $item->harga = $latestPrice;
                $item->subtotal = $latestPrice * $item->qty;
                $item->save();
            }
        }
    }

    /**
     * Cari voucher milik user berdasarkan kode, validasi status & minimum belanja.
     * Melempar Exception dengan pesan yang jelas kalau tidak valid.
     */
    private function resolveVoucher(Request $request, int $subtotal, bool $forUpdate = false): ?Voucher
    {
        $code = $request->voucher_code;
        if (empty($code)) {
            return null;
        }

        $query = Voucher::where('user_id', $request->user()->id)
            ->where('code', $code);

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        $voucher = $query->first();

        if (!$voucher || $voucher->is_used) {
            throw new Exception('Voucher tidak ditemukan atau sudah digunakan.');
        }

        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            throw new Exception('Voucher sudah kedaluwarsa.');
        }

        if ($subtotal < $voucher->min_purchase) {
            throw new Exception('Belanja belum memenuhi minimum Rp' . number_format($voucher->min_purchase, 0, ',', '.') . ' untuk voucher ini.');
        }

        return $voucher;
    }

    private function resolveShippingConfig(): array
    {
        $enabled = filter_var(SystemConfig::get('shipping_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $flatRate = max(0, (int) SystemConfig::get('shipping_flat_rate', 0));
        $effectiveCost = $enabled ? $flatRate : 0;

        return [
            'shipping_enabled' => $enabled,
            'shipping_flat_rate' => $flatRate,
            'shipping_cost' => $effectiveCost,
        ];
    }

    public function ctrl_checkout_pre(Request $request)
    {
        $this->syncCartPrices($request);

        $keranjang = Keranjang::select('id', 'barang_id', 'qty', 'harga', 'subtotal')
            ->with([
                'barang:id,nama_barang,kode_barang,satuan,deskripsi,rating',
                'barang.satuanBarang' => fn ($q) => $q->withTrashed()->select('id', 'nama'),
            ])
            ->where('user_id', $request->user()->id)
            ->get();

        foreach ($keranjang as $item) {
            if ($item->barang) {
                $satuanNama = $item->barang->resolvedSatuanName();
                $item->barang->setAttribute('satuan_nama', $satuanNama);
                $item->barang->setAttribute('satuan', $satuanNama);
            }
        }

        $subtotal_all = $keranjang->sum('subtotal');
        $total_point = $keranjang->count() > 0 ? intval($subtotal_all / SystemConfig::points_rate_per_point()) : 0;
        $minimumPurchase = (int) SystemConfig::get('minimum_purchase_amount', 0);
        $shipping = $this->resolveShippingConfig();

        $discount = 0;
        $voucherValid = true;
        $voucherError = null;

        if ($request->filled('voucher_code')) {
            try {
                $voucher = $this->resolveVoucher($request, $subtotal_all);
                $discount = $voucher ? (int) $voucher->calculateDiscount($subtotal_all) : 0;
            } catch (Exception $e) {
                $voucherValid = false;
                $voucherError = $e->getMessage();
            }
        }

        $totalAfterDiscount = max(0, $subtotal_all - $discount);

        return response()->json([
            'success' => true,
            'message' => 'Persiapan checkout berhasil ditampilkan',
            'total_items' => $keranjang->count(),
            'data' => $keranjang,
            'estimasi_poin' => $total_point,
            'subtotal_barang' => $subtotal_all,
            'minimum_purchase_amount' => $minimumPurchase,
            'is_eligible_checkout' => $subtotal_all >= $minimumPurchase,
            'shipping_enabled' => $shipping['shipping_enabled'],
            'shipping_flat_rate' => $shipping['shipping_flat_rate'],
            'shipping_cost' => $shipping['shipping_cost'],
            'voucher_code' => $request->voucher_code,
            'voucher_valid' => $voucherValid,
            'voucher_error' => $voucherError,
            'discount_amount' => $discount,
            'total_after_discount' => $totalAfterDiscount,
            'grand_total' => $totalAfterDiscount + $shipping['shipping_cost'],
        ]);
    }

    public function ctrl_checkout(Request $request)
    {
        DB::beginTransaction();

        try {
            $this->syncCartPrices($request);

            $keranjang = Keranjang::with('barang')->where('user_id', $request->user()->id)->get();

            if ($keranjang->count() == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang kosong',
                ], 400);
            }

            $subtotal_all = $keranjang->sum('subtotal');
            $total_point = $keranjang->count() > 0 ? intval($subtotal_all / SystemConfig::points_rate_per_point()) : 0;

            $minimumPurchase = (int) SystemConfig::get('minimum_purchase_amount', 0);
            if ($minimumPurchase > 0 && $subtotal_all < $minimumPurchase) {
                throw new Exception('Minimal belanja Rp' . number_format($minimumPurchase, 0, ',', '.') . ' untuk bisa checkout.');
            }

            $voucher = $this->resolveVoucher($request, $subtotal_all, true);
            $discount = $voucher ? $voucher->calculateDiscount($subtotal_all) : 0;

            $alamatId = $request->alamat ?: Order::alamatDefault($request);
            if (!$alamatId) {
                throw new Exception('Alamat pengiriman belum dipilih. Silakan tambahkan alamat terlebih dahulu.');
            }

            $alamat = Alamat::where('id', $alamatId)->where('user_id', auth()->id())->first();
            if (!$alamat) {
                throw new Exception('Alamat pengiriman tidak valid.');
            }

            foreach ($keranjang as $item) {
                $isActive = false;
                if ($request->user()->role == 'grosir' && $item->barang->hargaGrosir) {
                    $isActive = $item->barang->hargaGrosir->is_active;
                } elseif ($request->user()->role == 'ritel' && $item->barang->hargaRitel) {
                    $isActive = $item->barang->hargaRitel->is_active;
                }

                if (!$isActive) {
                    throw new Exception("Barang {$item->barang->nama_barang} tidak aktif atau stok habis. Silakan hapus dari keranjang.");
                }
            }

            // Kunci baris barang & cek ketersediaan + stok sebelum membuat order
            foreach ($keranjang as $item) {
                $barang = Barang::where('id', $item->barang_id)->lockForUpdate()->first();
                if (!$barang || !$barang->is_available) {
                    throw new Exception("Barang {$item->barang->nama_barang} sedang dinonaktifkan oleh toko.");
                }
                if ($barang->stock < $item->qty) {
                    throw new Exception("Stok {$item->barang->nama_barang} tidak mencukupi.");
                }
            }

            $shipping = $this->resolveShippingConfig();
            $shippingCost = $shipping['shipping_cost'];
            $grandTotal = max(0, $subtotal_all - $discount) + $shippingCost;

            $orderPayload = [
                'user_id' => auth()->id(),
                'invoice' => 'INV-' . time() . rand(100, 999),
                'total' => $grandTotal,
                'status' => 'pending',
                'alamat_pengiriman' => $alamat->id,
                'estimated_points' => $total_point,
                'metode_pembayaran_id' => 1,
                'voucher_id' => $voucher?->id,
                'discount_amount' => $discount,
            ];

            if (Schema::hasColumn('orders', 'shipping_cost')) {
                $orderPayload['shipping_cost'] = $shippingCost;
            }

            $order = Order::create($orderPayload);

            foreach ($keranjang as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'barang_id' => $item->barang_id,
                    'qty' => $item->qty,
                    'harga' => $item->harga,
                    'subtotal' => $item->subtotal,
                ]);

                Barang::where('id', $item->barang_id)->decrement('stock', $item->qty);
            }

            if ($voucher) {
                $voucher->update([
                    'is_used' => true,
                    'used_at' => now(),
                    'used_in_order_id' => $order->id,
                ]);
            }

            // Mail::to($request->user()->email)->send(new NotifCheckout($request->user(), $order, $keranjang));

            Keranjang::where('user_id', auth()->id())->delete();

            DB::commit();

            $order->load(['orderItems.barang', 'alamat_pengiriman']);

            return response()->json([
                'success' => true,
                'message' => 'Checkout berhasil!',
                'data' => $order,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Checkout gagal: ' . $e->getMessage()], 400);
        }
    }
}
