<?php

namespace App\Services;

use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\Voucher;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class LoyaltyService
{
    /**
     * Redeem a reward for a user.
     *
     * @param User $user
     * @param Reward $reward
     * @return RewardRedemption
     * @throws Exception
     */
    public function redeem(User $user, Reward $reward): RewardRedemption
    {
        // 1. Validasi Ketersediaan Reward
        if (!$reward->isAvailable()) {
            throw new Exception('Reward ini sedang tidak tersedia, stok habis, atau periode penukaran telah berakhir.');
        }

        // 2. Validasi Saldo Poin User
        if ($user->points < $reward->points_required) {
            throw new Exception('Poin Anda tidak mencukupi untuk menukarkan reward ini.');
        }

        return DB::transaction(function () use ($user, $reward) {
            // 3. Kurangi Stok Reward jika terbatas
            if ($reward->isPhysical() && !$reward->hasUnlimitedStock()) {
                $reward->decrement('stock');
            }

            // 4. Generate Kode Klaim untuk Barang Fisik / Voucher
            $claimCode = 'CLAIM-' . strtoupper(Str::random(8)) . '-' . time();

            // 5. Buat Data Reward Redemption
            $redemption = RewardRedemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_spent' => $reward->points_required,
                'claim_code' => $claimCode,
                'status' => 'pending',
                'expires_at' => Carbon::now()->addDays(7),
            ]);

            // 6. Buat Transaksi Point (Deduction)
            $newBalance = $user->points - $reward->points_required;
            PointTransaction::create([
                'user_id' => $user->id,
                'type' => 'redeem',
                'amount' => -$reward->points_required,
                'balance_after' => $newBalance,
                'reference_type' => RewardRedemption::class,
                'reference_id' => $redemption->id,
                'description' => 'Penukaran poin untuk reward: ' . $reward->resolvedName(),
            ]);

            // 7. Jika Reward berupa Voucher / Diskon, Buat Data Voucher
            if ($reward->isVoucher() || $reward->isDirect()) {
                Voucher::create([
                    'user_id' => $user->id,
                    'reward_redemption_id' => $redemption->id,
                    'code' => 'VCH-' . strtoupper(Str::random(6)) . '-' . rand(100, 999),
                    'discount_value' => $reward->discount_value ?? 0,
                    'discount_type' => $reward->discount_type ?? 'fixed',
                    'max_discount' => $reward->max_discount,
                    'min_purchase' => 0,
                    'is_used' => false,
                    'expires_at' => Carbon::now()->addDays(30), // Berlaku 30 hari sejak klaim
                ]);
            }

            return $redemption;
        });
    }

    /**
     * Refund points (and reward stock, if applicable) for a redemption that
     * is being cancelled or has expired. Caller is responsible for setting
     * the redemption's new status.
     */
    public function refundRedemption(RewardRedemption $redemption, string $reasonPrefix): void
    {
        DB::transaction(function () use ($redemption, $reasonPrefix) {
            $reward = $redemption->reward;

            if ($reward && $reward->isPhysical() && !$reward->hasUnlimitedStock()) {
                $reward->increment('stock');
            }

            $currentBalance = (int) PointTransaction::where('user_id', $redemption->user_id)->sum('amount');
            $newBalance = $currentBalance + $redemption->points_spent;

            PointTransaction::create([
                'user_id' => $redemption->user_id,
                'type' => 'adjustment',
                'amount' => $redemption->points_spent,
                'balance_after' => $newBalance,
                'reference_type' => RewardRedemption::class,
                'reference_id' => $redemption->id,
                'description' => 'Refund poin dari ' . $reasonPrefix . ' #' . ($redemption->claim_code ?? $redemption->id),
            ]);
        });
    }
}
