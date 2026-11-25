<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Order;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Kembalikan stok barang untuk setiap item pada order yang dibatalkan.
     */
    public function restoreStock(Order $order): void
    {
        foreach ($order->orderItems as $item) {
            Barang::where('id', $item->barang_id)->increment('stock', $item->qty);
        }
    }

    /**
     * Kembalikan voucher yang digunakan pada order yang dibatalkan agar bisa dipakai kembali.
     */
    public function restoreVoucher(Order $order): void
    {
        if ($order->voucher_id) {
            $voucher = \App\Models\Voucher::find($order->voucher_id);
            if ($voucher && $voucher->is_used) {
                $voucher->update([
                    'is_used' => false,
                    'used_at' => null,
                    'used_in_order_id' => null,
                ]);
            }
        }
    }


    /**
     * Award loyalty points for an order, once, when it reaches 'completed'.
     */
    public function awardPoints(Order $order): void
    {
        if ($order->estimated_points <= 0) {
            return;
        }

        $alreadyAwarded = PointTransaction::where('user_id', $order->user_id)
            ->where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('type', 'earn')
            ->exists();

        if ($alreadyAwarded) {
            return;
        }

        DB::transaction(function () use ($order) {
            $currentPoints = (int) PointTransaction::where('user_id', $order->user_id)->sum('amount');
            $newBalance = $currentPoints + $order->estimated_points;

            PointTransaction::create([
                'user_id' => $order->user_id,
                'type' => 'earn',
                'amount' => $order->estimated_points,
                'balance_after' => $newBalance,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'description' => 'Poin didapatkan dari pesanan #' . ($order->invoice ?? $order->id),
            ]);
        });
    }

    /**
     * Claw back previously-awarded points when a completed order is cancelled.
     */
    public function clawbackPoints(Order $order): void
    {
        if ($order->estimated_points <= 0) {
            return;
        }

        $alreadyAwarded = PointTransaction::where('user_id', $order->user_id)
            ->where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('type', 'earn')
            ->exists();

        $alreadyClawedBack = PointTransaction::where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('type', 'adjustment')
            ->exists();

        if (!$alreadyAwarded || $alreadyClawedBack) {
            return;
        }

        DB::transaction(function () use ($order) {
            $currentPoints = (int) PointTransaction::where('user_id', $order->user_id)->sum('amount');
            $newBalance = $currentPoints - $order->estimated_points;

            PointTransaction::create([
                'user_id' => $order->user_id,
                'type' => 'adjustment',
                'amount' => -$order->estimated_points,
                'balance_after' => $newBalance,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'description' => 'Penarikan poin karena pesanan #' . ($order->invoice ?? $order->id) . ' dibatalkan',
            ]);
        });
    }
}
